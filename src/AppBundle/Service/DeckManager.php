<?php


namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Deckslot;
use AppBundle\Entity\Mwl;
use AppBundle\Entity\Pack;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use AppBundle\Entity\Deckchange;

class DeckManager
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Judge $judge */
    private $judge;

    /** @var DiffService $diff */
    private $diff;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, Judge $judge, DiffService $diff, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->judge = $judge;
        $this->diff = $diff;
        $this->logger = $logger;
    }


    public function getByUser(User $user, bool $decode_variation = false)
    {
        $dbh = $this->entityManager->getConnection();
        $decks = $dbh->executeQuery(
            "SELECT
				d.id,
				d.name,
				DATE_FORMAT(d.date_creation, '%Y-%m-%dT%TZ') date_creation,
                DATE_FORMAT(d.date_update, '%Y-%m-%dT%TZ') date_update,
				d.description,
                d.tags,
                m.code mwl_code,
                (SELECT count(*) FROM deckchange c WHERE c.deck_id=d.id AND c.saved=0) unsaved,
                d.problem,
				c.title identity_title,
                c.code identity_code,
                c.image_url identity_url,
				f.code faction_code,
        		LPAD(y.position * 10 + p.position, 6, '0') lastpack_global_position,
                p.cycle_id cycle_id,
                p.position pack_number,
				s.name side
				FROM deck d
        		LEFT JOIN mwl m ON d.mwl_id=m.id
				LEFT JOIN card c ON d.identity_id=c.id
				LEFT JOIN faction f ON c.faction_id=f.id
				LEFT JOIN side s ON d.side_id=s.id
                LEFT JOIN pack p ON d.last_pack_id=p.id
        		LEFT JOIN cycle y ON p.cycle_id=y.id
				WHERE d.user_id=?
				ORDER BY date_update DESC",
            [
                $user->getId(),
            ]
        )
                     ->fetchAll();

        foreach ($decks as $i => $deck) {
            $decks[$i]['id'] = intval($deck['id']);
        }

        // slots

        $rows = $dbh->executeQuery(
            "SELECT
				s.deck_id,
				c.code card_code,
				s.quantity qty
				FROM deckslot s
				JOIN card c ON s.card_id=c.id
				JOIN deck d ON s.deck_id=d.id
				WHERE d.user_id=?",

            [
                $user->getId(),
            ]

        )
                    ->fetchAll();

        $cards = [];
        foreach ($rows as $row) {
            $deck_id = intval($row['deck_id']);
            unset($row['deck_id']);
            $row['qty'] = intval($row['qty']);
            if (!isset($cards[$deck_id])) {
                $cards[$deck_id] = [];
            }
            $cards[$deck_id][] = $row;
        }

        // changes

        $rows = $dbh->executeQuery(
            "SELECT
                DATE_FORMAT(c.date_creation, '%Y-%m-%dT%TZ') date_creation,
				c.variation,
                c.deck_id
				FROM deckchange c
				JOIN deck d ON c.deck_id=d.id
				WHERE d.user_id=? AND c.saved=1",

            [
                $user->getId(),
            ]

        )
                    ->fetchAll();

        $changes = [];
        foreach ($rows as $row) {
            $deck_id = intval($row['deck_id']);
            unset($row['deck_id']);
            if ($decode_variation) {
                $row['variation'] = json_decode($row['variation'], true);
            }
            if (!isset($changes[$deck_id])) {
                $changes[$deck_id] = [];
            }
            $changes[$deck_id][] = $row;
        }

        foreach ($decks as $i => $deck) {
            $decks[$i]['cards'] = $cards[$deck['id']];
            $decks[$i]['history'] = isset($changes[$deck['id']]) ? $changes[$deck['id']] : [];
            $decks[$i]['unsaved'] = intval($decks[$i]['unsaved']);
            $decks[$i]['tags'] = $deck['tags'] ? explode(' ', $deck['tags']) : [];

            $problem_message = '';
            if (isset($deck['problem'])) {
                $problem_message = $this->judge->problem($deck['problem']);
            }
            if ($decks[$i]['unsaved'] > 0) {
                $problem_message = "This deck has unsaved changes.";
            }

            $decks[$i]['message'] = $problem_message;
        }

        return $decks;
    }

    public function getById(int $deck_id, bool $decode_variation = false)
    {
        $dbh = $this->entityManager->getConnection();
        $deck = $dbh->executeQuery(
            "SELECT
				d.id,
				d.name,
				DATE_FORMAT(d.date_creation, '%Y-%m-%dT%TZ') date_creation,
				DATE_FORMAT(d.date_update, '%Y-%m-%dT%TZ') date_update,
				d.description,
                d.tags,
                m.code mwl_code,
                (SELECT count(*) FROM deckchange c WHERE c.deck_id=d.id AND c.saved=0) unsaved,
                d.problem,
				c.title identity_title,
                c.code identity_code,
				f.code faction_code,
				s.name side
				FROM deck d
        		LEFT JOIN mwl m ON d.mwl_id=m.id
				LEFT JOIN card c ON d.identity_id=c.id
				LEFT JOIN faction f ON c.faction_id=f.id
				LEFT JOIN side s ON d.side_id=s.id
				WHERE d.id=?",
            [
                $deck_id,
            ]
        )
                    ->fetch();

        $deck['id'] = intval($deck['id']);

        $rows = $dbh->executeQuery(
            "SELECT
				c.code card_code,
				s.quantity qty
				FROM deckslot s
				JOIN card c ON s.card_id=c.id
				JOIN deck d ON s.deck_id=d.id
				WHERE d.id=?",

            [
                $deck_id,
            ]

        )
                    ->fetchAll();

        $cards = [];
        foreach ($rows as $row) {
            $row['qty'] = intval($row['qty']);
            $cards[] = $row;
        }
        $deck['cards'] = $cards;

        $rows = $dbh->executeQuery(
            "SELECT
				DATE_FORMAT(c.date_creation, '%Y-%m-%dT%TZ') date_creation,
				c.variation
				FROM deckchange c
				WHERE c.deck_id=? AND c.saved=1
                ORDER BY date_creation DESC",

            [
                $deck_id,
            ]

        )
                    ->fetchAll();

        $changes = [];
        foreach ($rows as $row) {
            if ($decode_variation) {
                $row['variation'] = json_decode($row['variation'], true);
            }
            $changes[] = $row;
        }
        $deck['history'] = $changes;

        $deck['tags'] = $deck['tags'] ? explode(' ', $deck['tags']) : [];
        $problem = $deck['problem'];
        $deck['message'] = isset($problem) ? $this->judge->problem($problem) : '';

        return $deck;
    }

    /**
     * @param User        $user
     * @param Deck        $deck
     * @param int|null    $decklist_id
     * @param string      $name
     * @param string      $description
     * @param array       $tags
     * @param string|null $mwl_code
     * @param array       $content
     * @param Deck|null   $source_deck
     * @return int
     */
    public function saveDeck(
        User $user,
        Deck $deck,
        int $decklist_id = null,
        string $name,
        string $description,
        array $tags = [],
        string $mwl_code = null,
        array $content,
        Deck $source_deck = null
    ) {
        $deck_content = [];
        if ($decklist_id) {
            $decklist = $this->entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
            if ($decklist instanceof Decklist) {
                $deck->setParent($decklist);
            }
        }
        if ($mwl_code) {
            $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwl_code]);
            if ($mwl instanceof Mwl) {
                $deck->setMwl($mwl);
            }
        } else {
            $deck->setMwl(null);
        }

        $deck->setName($name);
        $deck->setDescription($description);
        $deck->setUser($user);
        $identity = null;
        $cards = [];
        $latestPack = null;
        foreach ($content as $card_code => $qty) {
            /** @var Card $card */
            $card = $this->entityManager->getRepository('AppBundle:Card')->findOneBy([
                "code" => $card_code,
            ]);
            if (!$card) {
                continue;
            }
            $pack = $card->getPack();
            if (!$latestPack instanceof Pack) {
                $latestPack = $pack;
            } elseif ($latestPack->getCycle()->getPosition() < $pack->getCycle()->getPosition()) {
                $latestPack = $pack;
            } elseif ($latestPack->getCycle()->getPosition() == $pack->getCycle()->getPosition() && $latestPack->getPosition() < $pack->getPosition()) {
                $latestPack = $pack;
            }
            if ($card->getType()->getCode() == "identity") {
                $identity = $card;
            }
            $cards[$card_code] = $card;
        }
        if ($latestPack instanceof Pack) {
            $deck->setLastPack($latestPack);
        }
        if ($identity) {
            $deck->setSide($identity->getSide());
            $deck->setIdentity($identity);
        } else {
            $deck->setSide(current($cards)->getSide());
            /** @var Card $identity */
            $identity = $this->entityManager->getRepository('AppBundle:Card')->findOneBy([
                "side" => $deck->getSide(),
            ]);
            $cards[$identity->getCode()] = $identity;
            $content[$identity->getCode()] = 1;
            $deck->setIdentity($identity);
        }
        if (count($tags) === 0) {
            // tags can never be empty. if it is we put faction in
            $faction_code = $identity->getFaction()->getCode();
            $tags = [$faction_code];
        }
        $deck->setTags(implode(' ', $tags));
        $this->entityManager->persist($deck);

        // on the deck content

        if ($source_deck) {
            // compute diff between current content and saved content
            list($listings) = $this->diff->diffContents([$content, $source_deck->getContent()]);
            // remove all change (autosave) since last deck update (changes are sorted)
            $changes = $this->getUnsavedChanges($deck);
            foreach ($changes as $change) {
                $this->entityManager->remove($change);
            }
            // save new change unless empty
            if (count($listings[0]) || count($listings[1])) {
                $change = new Deckchange();
                $change->setDeck($deck);
                $change->setVariation(json_encode($listings));
                $change->setSaved(true);
                $this->entityManager->persist($change);
            }
        }
        foreach ($deck->getSlots() as $slot) {
            $deck->removeSlot($slot);
            $this->entityManager->remove($slot);
        }

        foreach ($content as $card_code => $qty) {
            $card = $cards[$card_code];
            if ($card->getSide()->getId() != $deck->getSide()->getId()) {
                continue;
            }
            $card = $cards[$card_code];
            $slot = new Deckslot();
            $slot->setQuantity($qty);
            $slot->setCard($card);
            $slot->setDeck($deck);
            $deck->addSlot($slot);
            $deck_content[$card_code] = [
                'card' => $card,
                'qty'  => $qty,
            ];
        }
        $analyse = $this->judge->analyse($deck->getSlots()->toArray());
        if (is_string($analyse)) {
            $deck->setProblem($analyse);
        } else {
            $deck->setProblem(null);
            $deck->setDeckSize($analyse['deckSize']);
            $deck->setInfluenceSpent($analyse['influenceSpent']);
            $deck->setAgendaPoints($analyse['agendaPoints']);
        }
        $deck->setDateUpdate(new \DateTime());
        $this->entityManager->flush();

        return $deck->getId();
    }

    public function revertDeck(Deck $deck)
    {
        $changes = $this->getUnsavedChanges($deck);
        foreach ($changes as $change) {
            $this->entityManager->remove($change);
        }
        $this->entityManager->flush();
    }

    public function getUnsavedChanges(Deck $deck)
    {
        return $this->entityManager->getRepository('AppBundle:Deckchange')->findBy(['deck' => $deck, 'saved' => false]);
    }
}

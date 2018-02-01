<?php


namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Deckslot;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use AppBundle\Entity\Deckchange;

class Decks
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Judge $judge */
    private $judge;

    /** @var Diff $diff */
    private $diff;

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, Judge $judge, Diff $diff, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->judge = $judge;
        $this->diff = $diff;
        $this->logger = $logger;
    }
    

    public function getByUser(User $user, $decode_variation = false)
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
                (select count(*) from deckchange c where c.deck_id=d.id and c.saved=0) unsaved,
                d.problem,
				c.title identity_title,
                c.code identity_code,
                c.image_url identity_url,
				f.code faction_code,
        		LPAD(y.position * 10 + p.position, 6, '0') lastpack_global_position,
                p.cycle_id cycle_id,
                p.position pack_number,
				s.name side
				from deck d
        		left join mwl m on d.mwl_id=m.id
				left join card c on d.identity_id=c.id
				left join faction f on c.faction_id=f.id
				left join side s on d.side_id=s.id
                left join pack p on d.last_pack_id=p.id
        		left join cycle y on p.cycle_id=y.id
				where d.user_id=?
				order by date_update desc",
            array(
                        $user->getId()
                )
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
				from deckslot s
				join card c on s.card_id=c.id
				join deck d on s.deck_id=d.id
				where d.user_id=?",
        
            array(
                        $user->getId()
                )
        
        )
            ->fetchAll();
        
        $cards = array();
        foreach ($rows as $row) {
            $deck_id = intval($row['deck_id']);
            unset($row['deck_id']);
            $row['qty'] = intval($row['qty']);
            if (! isset($cards[$deck_id])) {
                $cards[$deck_id] = array();
            }
            $cards[$deck_id][] = $row;
        }
        
        // changes
        
        $rows = $dbh->executeQuery(
                "SELECT
                DATE_FORMAT(c.date_creation, '%Y-%m-%dT%TZ') date_creation,
				c.variation,
                c.deck_id
				from deckchange c
				join deck d on c.deck_id=d.id
				where d.user_id=? and c.saved=1",
        
            array(
                    $user->getId()
            )
        
        )
            ->fetchAll();
        
        $changes = array();
        foreach ($rows as $row) {
            $deck_id = intval($row['deck_id']);
            unset($row['deck_id']);
            if ($decode_variation) {
                $row['variation'] = json_decode($row['variation'], true);
            }
            if (! isset($changes[$deck_id])) {
                $changes[$deck_id] = array();
            }
            $changes[$deck_id][] = $row;
        }
        
        foreach ($decks as $i => $deck) {
            $decks[$i]['cards'] = $cards[$deck['id']];
            $decks[$i]['history'] = isset($changes[$deck['id']]) ? $changes[$deck['id']] : array();
            $decks[$i]['unsaved'] = intval($decks[$i]['unsaved']);
            $decks[$i]['tags'] = $deck['tags'] ? explode(' ', $deck['tags']) : array();
            
            $problem_message = '';
            if (isset($deck['problem'])) {
                $problem_message = $this->judge->problem($deck['problem']);
            }
            if ($decks[$i]['unsaved'] > 0) {
                $problem_message = "This deck has unsaved changes.";
            }
            
            $decks[$i]['message'] =  $problem_message;
        }
        
        return $decks;
    }

    public function getById($deck_id, $decode_variation = false)
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
                (select count(*) from deckchange c where c.deck_id=d.id and c.saved=0) unsaved,
                d.problem,
				c.title identity_title,
                c.code identity_code,
				f.code faction_code,
				s.name side
				from deck d
        		left join mwl m on d.mwl_id=m.id
				left join card c on d.identity_id=c.id
				left join faction f on c.faction_id=f.id
				left join side s on d.side_id=s.id
				where d.id=?",
            array(
                        $deck_id
                )
        )
            ->fetch();
        
        $deck['id'] = intval($deck['id']);
        
        $rows = $dbh->executeQuery(
                "SELECT
				c.code card_code,
				s.quantity qty
				from deckslot s
				join card c on s.card_id=c.id
				join deck d on s.deck_id=d.id
				where d.id=?",
        
            array(
                        $deck_id
                )
        
        )
            ->fetchAll();
        
        $cards = array();
        foreach ($rows as $row) {
            $row['qty'] = intval($row['qty']);
            $cards[] = $row;
        }
        $deck['cards'] = $cards;
        
        $rows = $dbh->executeQuery(
                "SELECT
				DATE_FORMAT(c.date_creation, '%Y-%m-%dT%TZ') date_creation,
				c.variation
				from deckchange c
				where c.deck_id=? and c.saved=1
                order by date_creation desc",
        
            array(
                                $deck_id
                        )
        
        )
                        ->fetchAll();
        
        $changes = array();
        foreach ($rows as $row) {
            if ($decode_variation) {
                $row['variation'] = json_decode($row['variation'], true);
            }
            $changes[] = $row;
        }
        $deck['history'] = $changes;
        
        $deck['tags'] = $deck['tags'] ? explode(' ', $deck['tags']) : array();
        $problem = $deck['problem'];
        $deck['message'] = isset($problem) ? $this->judge->problem($problem) : '';
        
        return $deck;
    }
    

    public function saveDeck(User $user, Deck $deck, $decklist_id, $name, $description, $tags, $mwl_code, $content, Deck $source_deck)
    {
        $deck_content = array();
        if ($decklist_id) {
            $decklist = $this->entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
            if ($decklist) {
                $deck->setParent($decklist);
            }
        }
        if ($mwl_code) {
            $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwl_code]);
            if ($mwl) {
                $deck->setMwl($mwl);
            }
        } else {
            $deck->setMwl(null);
        }
        
        $deck->setName($name);
        $deck->setDescription($description);
        $deck->setUser($user);
        $identity = null;
        $cards = array();
        /* @var $latestPack \AppBundle\Entity\Pack */
        $latestPack = null;
        foreach ($content as $card_code => $qty) {
            /** @var Card $card */
            $card = $this->entityManager->getRepository('AppBundle:Card')->findOneBy(array(
                    "code" => $card_code
            ));
            if (!$card) {
                continue;
            }
            $pack = $card->getPack();
            if (! $latestPack) {
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
        $deck->setLastPack($latestPack);
        if ($identity) {
            $deck->setSide($identity->getSide());
            $deck->setIdentity($identity);
        } else {
            $deck->setSide(current($cards)->getSide());
            /** @var Card $identity */
            $identity = $this->entityManager->getRepository('AppBundle:Card')->findOneBy(array(
                    "side" => $deck->getSide()
            ));
            $cards[$identity->getCode()] = $identity;
            $content[$identity->getCode()] = 1;
            $deck->setIdentity($identity);
        }
        if (empty($tags)) {
            // tags can never be empty. if it is we put faction in
            $faction_code = $identity->getFaction()->getCode();
            $tags = array($faction_code);
        }
        if (is_array($tags)) {
            $tags = implode(' ', $tags);
        }
        $deck->setTags($tags);
        $this->entityManager->persist($deck);
        
        // on the deck content
        
        if ($source_deck) {
            // compute diff between current content and saved content
            list($listings) = $this->diff->diffContents(array($content, $source_deck->getContent()));
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
            $deck_content[$card_code] = array(
                    'card' => $card,
                    'qty' => $qty
            );
        }
        $analyse = $this->judge->analyse($deck->getSlots());
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

    public function revertDeck($deck)
    {
        $changes = $this->getUnsavedChanges($deck);
        foreach ($changes as $change) {
            $this->entityManager->remove($change);
        }
        $this->entityManager->flush();
    }
    
    public function getUnsavedChanges($deck)
    {
        return $this->entityManager->getRepository('AppBundle:Deckchange')->findBy(array('deck' => $deck, 'saved' => false));
    }
}

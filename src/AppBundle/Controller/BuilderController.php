<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Deckchange;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Deckslot;
use AppBundle\Entity\Mwl;
use AppBundle\Entity\User;
use AppBundle\Service\CardsData;
use AppBundle\Service\DeckManager;
use AppBundle\Service\Judge;
use AppBundle\Service\TextProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BuilderController extends Controller
{
    /**
     * @param string                 $side_text
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function buildformAction(string $side_text, EntityManagerInterface $entityManager, CardsData $cardsData, LoggerInterface $logger)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        $side = $entityManager->getRepository('AppBundle:Side')->findOneBy([
            "name" => $side_text,
        ]);
        $type = $entityManager->getRepository('AppBundle:Type')->findOneBy([
            "code" => "identity",
        ]);

        $identities = $entityManager->getRepository('AppBundle:Card')->findBy([
            "type" => $type,
        ], [
            "faction" => "ASC",
            "title"   => "ASC",
        ]);
        $identities = $cardsData->select_only_latest_cards($identities);
        // Sorting minifactions and neutrals like this because whatever PHP version this is can't do stable sorting
        // Resulting array is [ normal factions ... mini factions ... neutral ]
        $identities = array_merge(
          array_filter($identities, function($identity) {
            return !$identity->getFaction()->getIsNeutral() && !$identity->getFaction()->getIsMini();
          }),
          array_filter($identities, function($identity) {
            return $identity->getFaction()->getIsMini();
          }),
          array_filter($identities, function($identity) {
            return $identity->getFaction()->getIsNeutral();
          }),
        );

        $factions = $entityManager->getRepository('AppBundle:Faction')->findBy([], [
            "name" => "ASC",
        ]);
        $corp_factions = array_filter($factions, function($faction) {
          return $faction->getSide()->getCode() == "corp" && !$faction->getIsNeutral();
        });
        $runner_factions = array_filter($factions, function($faction) {
          return $faction->getSide()->getCode() == "runner" && !$faction->getIsNeutral() && !$faction->getIsMini();
        });

        $banned_cards = array();
        foreach ($identities as $id) {
            $i = $cardsData->get_mwl_info([$id], true /* active_only */);
            if (count($i) > 0 && $i[array_keys($i)[0]]['active'] && $i[array_keys($i)[0]]['deck_limit'] === 0) {
                $banned_cards[$id->getCode()] = true;
            }
         }

        return $this->render(

            '/Builder/initbuild.html.twig',
            [
                'pagetitle'       => "New deck",
                'pagedescription' => "Choose your identity to start building a custom deck.",
                "identities"      => $identities,
                "banned_cards"    => $banned_cards,
                "corp_factions"   => $corp_factions,
                "runner_factions" => $runner_factions,
                "side"            => $side,
            ],

            $response

        );
    }

    /**
     * @param string                 $card_code
     * @param EntityManagerInterface $entityManager
     * @param CardsData              $cardsData
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function initbuildAction(string $card_code, EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        /** @var Card $card */
        $card = $entityManager->getRepository('AppBundle:Card')->findOneBy([
            "code" => $card_code,
        ]);
        if (!$card) {
            return new Response('card not found.');
        }

        $list_mwl = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);
        /** @var Mwl|null $active_mwl */
        $active_mwl = $entityManager->getRepository('AppBundle:Mwl')->findOneBy(['active' => true]);

        $arr = [
            $card_code => 1,
        ];

        return $this->render(
            '/Builder/deck.html.twig',
            [
                'pagetitle'           => "Deckbuilder",
                'pagedescription'     => "Build your own custom deck with the help of a powerful deckbuilder.",
                'deck'                => [
                    'side_name'   => mb_strtolower($card->getSide()
                                                        ->getName()),
                    "slots"       => $arr,
                    "name"        => "New " . $card->getSide()
                                                   ->getName() . " Deck",
                    "description" => "",
                    "tags"        => $card->getFaction()->getCode(),
                    "id"          => "",
                    "uuid"        => "",
                    "history"     => [],
                    "unsaved"     => 0,
                    "mwl_code"    => $active_mwl ? $active_mwl->getCode() : null,
                ],
                "published_decklists" => [],
                "list_mwl"            => $list_mwl,
            ],
            $response
        );
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function importAction(EntityManagerInterface $entityManager)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        $list_mwl = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);

        return $this->render(

            '/Builder/directimport.html.twig',
            [
                'pagetitle' => "Import a deck",
                'pagedescription' => "Import a deck from outside of NetrunnerDB.",
                'list_mwl'  => $list_mwl,
            ],

            $response

        );
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function fileimportAction(Request $request, EntityManagerInterface $entityManager)
    {
        $filetype = filter_var($request->get('type'), FILTER_SANITIZE_STRING);
        $uploadedFile = $request->files->get('upfile');
        if (!isset($uploadedFile)) {
            return new Response('No file');
        }
        $origname = $uploadedFile->getClientOriginalName();
        $origext = $uploadedFile->getClientOriginalExtension();
        $filename = $uploadedFile->getPathname();

        if (function_exists("finfo_open")) {
            // return mime type ala mimetype extension
            $finfo = finfo_open(FILEINFO_MIME);

            // check to see if the mime-type starts with 'text'
            $is_text = substr(finfo_file($finfo, $filename), 0, 4) == 'text' || substr(finfo_file($finfo, $filename), 0, 15) == "application/xml";
            if (!$is_text) {
                return new Response('Bad file');
            }
        }

        if ($filetype == "octgn" || ($filetype == "auto" && $origext == "o8d")) {
            $parse = $this->parseOctgnImport(file_get_contents($filename), $entityManager);
        } else {
            $parse = $this->parseTextImport(file_get_contents($filename), $entityManager);
        }

        return $this->forward(
            'AppBundle:Builder:save',
            [
                'name'        => $origname,
                'content'     => json_encode($parse['content']),
                'description' => $parse['description'],
            ]
        );
    }

    /**
     * @param string                 $text
     * @param EntityManagerInterface $entityManager
     * @return array
     */
    public function parseTextImport(string $text, EntityManagerInterface $entityManager)
    {
        $content = [];
        $lines = explode("\n", $text);
        $identity = null;
        foreach ($lines as $line) {
            $matches = [];
            if (preg_match('/^\s*(\d)x?([\pLl\pLu\pN\-\.\'\!\: ]+)/u', $line, $matches)) {
                $quantity = intval($matches[1]);
                $name = trim($matches[2]);
            } elseif (preg_match('/^([^\(]+).*x(\d)/', $line, $matches)) {
                $quantity = intval($matches[2]);
                $name = trim($matches[1]);
            } elseif (empty($identity) && preg_match('/([^\(]+):([^\(]+)/', $line, $matches)) {
                $quantity = 1;
                $name = trim($matches[1] . ":" . $matches[2]);
                $identity = $name;
            } else {
                continue;
            }
            $card = $entityManager->getRepository('AppBundle:Card')->findOneBy([
                'title' => $name,
            ]);
            if ($card instanceof Card) {
                $content[$card->getCode()] = $quantity;
            }
        }

        return [
            "content"     => $content,
            "description" => "",
        ];
    }

    /**
     * @param string                 $octgn
     * @param EntityManagerInterface $entityManager
     * @return array
     */
    public function parseOctgnImport(string $octgn, EntityManagerInterface $entityManager)
    {
        $crawler = new Crawler();
        $crawler->addXmlContent($octgn);
        $cardcrawler = $crawler->filter('deck > section > card');

        $content = [];
        foreach ($cardcrawler as $domElement) {
            $quantity = intval($domElement->getAttribute('qty'));
            $matches = [];
            if (preg_match('/bc0f047c-01b1-427f-a439-d451eda(\d{5})/', $domElement->getAttribute('id'), $matches)) {
                $card_code = $matches[1];
            } else {
                continue;
            }
            $card = $entityManager->getRepository('AppBundle:Card')->findOneBy([
                'code' => $card_code,
            ]);
            if ($card instanceof Card) {
                $content[$card->getCode()] = $quantity;
            }
        }

        $desccrawler = $crawler->filter('deck > notes');
        $description = [];
        foreach ($desccrawler as $domElement) {
            $description[] = $domElement->nodeValue;
        }

        return [
            "content"     => $content,
            "description" => implode("\n", $description),
        ];
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param DeckManager            $deckManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function meteorimportAction(Request $request, EntityManagerInterface $entityManager, DeckManager $deckManager)
    {
        // first build an array to match meteor card names with our card codes
        $glossary = [];
        $cards = $entityManager->getRepository('AppBundle:Card')->findAll();
        /** @var Card $card */
        foreach ($cards as $card) {
            $title = $card->getTitle();
            $replacements = [
                'Alix T4LB07'          => 'Alix T4LBO7',
                'Planned Assault'      => 'Planned Attack',
                'Security Testing'     => 'Security Check',
                'Mental Health Clinic' => 'Psychiatric Clinic',
                'Shi.Kyū'              => 'Shi Kyu',
                'NeoTokyo Grid'        => 'NeoTokyo City Grid',
                'Push Your Luck'       => 'Double or Nothing',
            ];
            if (isset($replacements[$title])) {
                $title = $replacements[$title];
            }
            // rule to cut the subtitle of an identity
            if ($card->getPack()
                     ->getCycle()
                     ->getPosition() < 2 || ($card->getPack()
                                                  ->getCycle()
                                                  ->getPosition() == 2 && $card->getSide()->getName() == "Runner")) {
                $title = preg_replace('~:.*~', '', $title);
            }

            $pack = $card->getPack()->getName();
            if ($pack == "Core Set") {
                $pack = "Core";
            }

            $str = $title . " " . $pack;

            $str = str_replace('\'', '', $str);
            $str = strtr(
                utf8_decode($str),
                utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿō'),
                'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyyo'
            );
            $str = strtolower($str);
            $str = preg_replace('~\W+~', '-', $str);
            $glossary[$str] = $card->getCode();
        }

        $url = $request->request->get('urlmeteor');
        $matches = [];
        if (!preg_match('~http://netrunner.meteor.com/users/([^/]+)~', $url, $matches)) {
            $this->addFlash('error', "Wrong URL. Please go to \"Your decks\" on Meteor DeckManager and copy the content of the address bar into the required field.");

            return $this->redirect($this->generateUrl('decks_list'));
        }
        $meteor_id = $matches[1];
        $meteor_json = file_get_contents("http://netrunner.meteor.com/api/decks/$meteor_id");
        $meteor_data = json_decode($meteor_json, true);

        // check to see if the user has enough available deck slots
        /** @var User $user */
        $user = $this->getUser();
        $slots_left = $user->getMaxNbDecks() - $user->getDecks()->count();
        $slots_required = count($meteor_data);
        if ($slots_required > $slots_left) {
            $this->addFlash(
                     'error',
                     "You don't have enough available deck slots to import the $slots_required decks from Meteor (only $slots_left slots left). You must either delete some decks here or on Meteor DeckManager."
                 );

            return $this->redirect($this->generateUrl('decks_list'));
        }

        foreach ($meteor_data as $meteor_deck) {
            // add a tag for side and faction of deck
            $identity_code = $glossary[$meteor_deck['identity']];
            /** @var Card $identity */
            $identity = $entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $identity_code]);
            if (!$identity) {
                continue;
            }
            $faction_code = $identity->getFaction()->getCode();
            $side_code = $identity->getSide()->getCode();
            $tags = [$faction_code, $side_code];

            $content = [
                $identity_code => 1,
            ];
            foreach ($meteor_deck['entries'] as $entry => $qty) {
                if (!isset($glossary[$entry])) {
                    $this->addFlash('error', "Error importing a deck. The name \"$entry\" doesn't match any known card. Please contact the administrator.");

                    return $this->redirect($this->generateUrl('decks_list'));
                }
                $content[$glossary[$entry]] = $qty;
            }

            /** @var Deck $deck */
            $deck = new Deck();
            $deckManager->saveDeck($this->getUser(), $deck, null, $meteor_deck['name'], "", $tags, null, $content, null);
        }

        $this->addFlash('notice', "Successfully imported $slots_required decks from Meteor DeckManager.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Deck $deck
     * @param Judge $judge
     * @param CardsData $cardsData
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     *
     * @ParamConverter("deck", class="AppBundle:Deck", options={"mapping": {"deck_uuid": "uuid"}})
     */
    public function textExportAction(Deck $deck, Judge $judge, CardsData $cardsData)
    {
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $response = new Response();

        $name = mb_strtolower($deck->getName());
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
        $name = preg_replace('/--+/', '-', $name);

        $lines = [$name];
        $types = [
            "Event",
            "Hardware",
            "Resource",
            "Icebreaker",
            "Program",
            "Agenda",
            "Asset",
            "Upgrade",
            "Operation",
            "Barrier",
            "Code Gate",
            "Sentry",
            "Ice",
        ];

        $lines[] = sprintf(
            '%s (%s)',
            $deck->getIdentity()->getTitle(),
            $deck->getIdentity()->getPack()->getName()
        );

        $classement = $judge->classe($deck->getSlots()->toArray(), $deck->getIdentity());

        if (isset($classement['problem'])) {
            $lines[] = sprintf(
                "Warning: %s",
                $classement['problem']
            );
        }

        $mwl = null;
        if ($deck->getMWL()) {
            $mwl = $deck->getMWL()->getCards();
        }
        foreach ($types as $type) {
            if (isset($classement[$type]) && $classement[$type]['qty']) {
                $lines[] = "";
                $lines[] = $type . " (" . $classement[$type]['qty'] . ")";
                foreach ($classement[$type]['slots'] as $slot) {
                    $inf = "";

                    /** @var Card $card */
                    $card = $slot['card'];
                    $is_restricted = (
                        $mwl
                        && isset($mwl[$card->getCode()])
                        && isset($mwl[$card->getCode()]['is_restricted'])
                        && ($mwl[$card->getCode()]['is_restricted'] === 1)
                    );

                    if ($is_restricted) {
                        $inf .= "♘";
                    }

                    for ($i = 0; $i < $slot['influence']; $i++) {
                        if ($i % 5 == 0) {
                            $inf .= " ";
                        }
                        $inf .= "•";
                    }

                    $lines[] = sprintf(
                        '%sx %s (%s) %s',
                        $slot['qty'],
                        $card->getTitle(),
                        $card->getPack()->getName(),
                        trim($inf)
                    );
                }
            }
        }

        $lines[] = "";

        $influenceSpent = $classement['influenceSpent'];
        $influenceTotal = $deck->getIdentity()->getInfluenceLimit();
        $influenceLeft = 0;
        if (is_numeric($influenceTotal)) {
            $influenceLeft = $influenceTotal - $influenceSpent;
        } else {
            $influenceTotal = "infinite";
        }

        $lines[] = sprintf(
            "%s influence spent (max %s, available %s)",
            $influenceSpent,
            $influenceTotal,
            $influenceLeft
        );

        if ($deck->getSide()->getCode() == "corp") {
            $minAgendaPoints = floor($deck->getDeckSize() / 5) * 2 + 2;

            $lines[] = sprintf(
                "%s agenda points (between %s and %s)",
                $classement['agendaPoints'],
                $minAgendaPoints,
                $minAgendaPoints + 1
            );
        }

        $lines[] = sprintf(
            "%s cards (min %s)",
            $classement['deckSize'],
            $deck->getIdentity()->getMinimumDeckSize()
        );

        $lines[] = "Cards up to " . $deck->getLastPack()->getName();
        $content = implode("\r\n", $lines);

        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $name . ".txt");

        $response->setContent($content);

        return $response;
    }

    /**
     * @param Deck $deck
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     *
     * @ParamConverter("deck", class="AppBundle:Deck", options={"mapping": {"deck_uuid": "uuid"}})
     */
    public function octgnExportAction(Deck $deck)
    {
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $rd = [];
        $identity = null;
        /** @var Deckslot $slot */
        foreach ($deck->getSlots() as $slot) {
            if ($slot->getCard()
                     ->getType()
                     ->getName() == "Identity") {
                $identity = [
                    "index" => $slot->getCard()->getCode(),
                    "name"  => $slot->getCard()->getTitle(),
                ];
            } else {
                $rd[] = [
                    "index" => $slot->getCard()->getCode(),
                    "name"  => $slot->getCard()->getTitle(),
                    "qty"   => $slot->getQuantity(),
                ];
            }
        }
        $name = mb_strtolower($deck->getName());
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
        $name = preg_replace('/--+/', '-', $name);
        if (empty($identity)) {
            return new Response('no identity found');
        }

        $filename = "$name.o8d";

        $content = $this->renderView(
            '/octgn.xml.twig',
            [
                "identity"    => $identity,
                "rd"          => $rd,
                "description" => strip_tags($deck->getDescription()),
            ]
        );

        $response = new Response();

        $response->headers->set('Content-Type', 'application/octgn');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $filename);

        $response->setContent($content);

        return $response;
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param DeckManager            $deckManager
     * @param TextProcessor          $textProcessor
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function saveAction(Request $request, EntityManagerInterface $entityManager, DeckManager $deckManager, TextProcessor $textProcessor)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getDecks()->count() > $user->getMaxNbDecks()) {
            return new Response('You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.');
        }

        $id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        $deck = null;
        $source_deck = null;
        if ($id) {
            $deck = $entityManager->getRepository('AppBundle:Deck')->find($id);
            if (!$deck instanceof Deck || $user->getId() != $deck->getUser()->getId()) {
                throw $this->createAccessDeniedException();
            }
            $source_deck = $deck;
        }

        $cancel_edits = (boolean) filter_var($request->get('cancel_edits'), FILTER_SANITIZE_NUMBER_INT);
        if ($cancel_edits) {
            if ($deck) {
                $deckManager->revertDeck($deck);
            }

            return $this->redirect($this->generateUrl('decks_list'));
        }

        $is_copy = (boolean) filter_var($request->get('copy'), FILTER_SANITIZE_NUMBER_INT);
        if ($is_copy || !$id) {
            $deck = new Deck();
            $entityManager->persist($deck);
        }

        $content = (array) json_decode($request->get('content'));
        if (!count($content)) {
            return new Response('Cannot import empty deck');
        }
        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = intval(filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT));
        $description = $textProcessor->purify(trim($request->get('description')));
        $tags = explode(',', filter_var($request->get('tags'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        $mwl_code = $request->get('format_casual') ? null : $request->get('mwl_code');

        if ($deck instanceof Deck) {
            $deckManager->saveDeck($this->getUser(), $deck, $decklist_id, $name, $description, $tags, $mwl_code, $content, $source_deck ? $source_deck : null);
        }

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager)
    {
        $deck_uuid = $request->get('deck_uuid');
        $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(['uuid' => $request->get('deck_uuid')]);
        if (!$deck instanceof Deck) {
            return $this->redirect($this->generateUrl('decks_list'));
        }
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        foreach ($deck->getChildren() as $decklist) {
            $decklist->setParent(null);
        }
        $entityManager->remove($deck);
        $entityManager->flush();

        $this->addFlash('notice', "Deck deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function deleteListAction(Request $request, EntityManagerInterface $entityManager)
    {
        $list_uuid = explode(',', $request->get('uuids'));

        foreach ($list_uuid as $uuid) {
            /** @var Deck $deck */
            $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(["uuid" => $uuid]);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }

            foreach ($deck->getChildren() as $decklist) {
                $decklist->setParent(null);
            }
            $entityManager->remove($deck);
        }
        $entityManager->flush();

        $this->addFlash('notice', "Decks deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param string                    $deck_uuid
     * @param EntityManagerInterface    $entityManager
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function editAction(string $deck_uuid, EntityManagerInterface $entityManager)
    {
        $dbh = $entityManager->getConnection();
        $rows = $dbh->executeQuery("
            SELECT
              d.id,
              d.uuid,
              d.name,
              m.code mwl_code,
              DATE_FORMAT(d.date_creation, '%Y-%m-%dT%TZ') date_creation,
              DATE_FORMAT(d.date_update, '%Y-%m-%dT%TZ') date_update,
              d.description,
              d.tags,
              u.id user_id,
              (SELECT count(*) FROM deckchange c WHERE c.deck_id=d.id AND c.saved=0) unsaved,
              s.name side_name
            FROM deck d
              LEFT JOIN mwl m ON d.mwl_id=m.id
              LEFT JOIN user u ON d.user_id=u.id
              LEFT JOIN side s ON d.side_id=s.id
            WHERE
              d.uuid = ?", [$deck_uuid])->fetchAll();
        $deck = $rows[0];

        if ($this->getUser()->getId() != $deck['user_id']) {
            throw $this->createAccessDeniedException();
        }

        $deck['side_name'] = mb_strtolower($deck['side_name']);

        $rows = $dbh->executeQuery("
            SELECT
              c.code,
              s.quantity
            FROM deckslot s
              JOIN card c ON s.card_id=c.id
            WHERE s.deck_id=?", [
            $deck['id'],
        ])->fetchAll();

        $cards = [];
        foreach ($rows as $row) {
            $cards[$row['code']] = intval($row['quantity']);
        }

        $snapshots = [];

        $rows = $dbh->executeQuery("
            SELECT
              DATE_FORMAT(c.date_creation, '%Y-%m-%dT%TZ') date_creation,
              c.variation,
              c.saved
            FROM deckchange c
            WHERE c.deck_id=? AND c.saved=1
            ORDER BY date_creation DESC", [$deck['id']])->fetchAll();

        // recreating the versions with the variation info, starting from $preversion
        $preversion = $cards;
        foreach ($rows as $row) {
            $row['variation'] = $variation = json_decode($row['variation'], true);
            $row['saved'] = (boolean) $row['saved'];
            // add preversion with variation that lead to it
            $row['content'] = $preversion;
            array_unshift($snapshots, $row);

            // applying variation to create 'next' (older) preversion
            foreach ($variation[0] as $code => $qty) {
                $preversion[$code] = $preversion[$code] - $qty;
                if ($preversion[$code] == 0) {
                    unset($preversion[$code]);
                }
            }
            foreach ($variation[1] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    $preversion[$code] = 0;
                }
                $preversion[$code] = $preversion[$code] + $qty;
            }
            ksort($preversion);
        }

        // add last know version with empty diff
        $row['content'] = $preversion;
        $row['date_creation'] = $deck['date_creation'];
        $row['saved'] = true;
        $row['variation'] = null;
        array_unshift($snapshots, $row);

        $rows = $dbh->executeQuery("
            SELECT
              DATE_FORMAT(c.date_creation, '%Y-%m-%dT%TZ') date_creation,
              c.variation,
              c.saved
            FROM deckchange c
            WHERE c.deck_id=? AND c.saved=0
            ORDER BY date_creation ASC", [$deck['id']])->fetchAll();

        // recreating the snapshots with the variation info, starting from $postversion
        $postversion = $cards;
        foreach ($rows as $row) {
            $row['variation'] = $variation = json_decode($row['variation'], true);
            $row['saved'] = (boolean) $row['saved'];
            // applying variation to postversion
            foreach ($variation[0] as $code => $qty) {
                if (!isset($postversion[$code])) {
                    $postversion[$code] = 0;
                }
                $postversion[$code] = $postversion[$code] + $qty;
            }
            foreach ($variation[1] as $code => $qty) {
                $postversion[$code] = $postversion[$code] - $qty;
                if ($postversion[$code] == 0) {
                    unset($postversion[$code]);
                }
            }
            ksort($postversion);

            // add postversion with variation that lead to it
            $row['content'] = $postversion;
            array_push($snapshots, $row);
        }

        // current deck is newest snapshot
        $deck['slots'] = $postversion;

        $deck['history'] = $snapshots;

        $published_decklists = $dbh->executeQuery(
            "SELECT
               d.id,
               d.uuid,
               d.name,
               d.prettyname,
               d.nbvotes,
               d.nbfavorites,
               d.nbcomments
             FROM decklist d
             WHERE d.parent_deck_id=?
               AND d.moderation_status IN (0,1)
             ORDER BY d.date_creation ASC",

            [
                $deck['id'],
            ]

        )->fetchAll();

        $parent_decklists = $dbh->executeQuery(
            "SELECT
               d.id,
               d.uuid,
               d.name,
               d.prettyname,
               d.nbvotes,
               d.nbfavorites,
               d.nbcomments
             FROM decklist d, deck
             WHERE deck.id = ? AND d.id=deck.parent_decklist_id
               AND d.moderation_status IN (0,1)
             ORDER BY d.date_creation ASC",

            [
                $deck['id'],
            ]

        )->fetchAll();

        $list_mwl = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);

        return $this->render(
            '/Builder/deck.html.twig',
            [
                'pagetitle'           => "Deckbuilder",
                'pagedescription'     => "Build your own custom deck with the help of a powerful deckbuilder.",
                'deck'                => $deck,
                'published_decklists' => $published_decklists,
                'parent_decklists'    => $parent_decklists,
                'list_mwl'            => $list_mwl,
            ]
        );
    }

    /**
     * @param int                    $deck_id
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function legacyViewAction(int $deck_id, EntityManagerInterface $entityManager) {
      $deck = $entityManager->getRepository('AppBundle:Deck')->find($deck_id);
      if ($deck) {
        return $this->redirect(
            $this->generateUrl('deck_view', ['deck_uuid' => $deck->getUuid()]),
            301);
      } else {
        throw $this->createNotFoundException();
      }
    }

    /**
     * @param string                 $deck_uuid
     * @param EntityManagerInterface $entityManager
     * @param Judge                  $judge
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function viewAction(string $deck_uuid, EntityManagerInterface $entityManager, Judge $judge) {
        $dbh = $entityManager->getConnection();
        $rows = $dbh->executeQuery("
            SELECT
              d.id,
              d.uuid,
              d.name,
              d.description,
              m.code,
              d.problem,
              d.date_update,
              s.name side_name,
              c.code identity_code,
              f.code faction_code,
              u.username user_name,
              CASE WHEN u.id=? THEN 1 ELSE 0 END is_owner
            FROM deck d
              LEFT JOIN mwl m  ON d.mwl_id=m.id
              LEFT JOIN user u ON d.user_id=u.id
              LEFT JOIN side s ON d.side_id=s.id
              LEFT JOIN card c ON d.identity_id=c.id
              LEFT JOIN faction f ON c.faction_id=f.id
            WHERE (u.id=? OR u.share_decks=1) AND d.uuid = ?",
            [
                $this->getUser() ? $this->getUser()->getId() : null,
                $this->getUser() ? $this->getUser()->getId() : null,
                $deck_uuid,
            ]
        )->fetchAll();

        if (!count($rows)) {
            throw $this->createNotFoundException();
        }

        $deck = $rows[0];

        $deck['side_name'] = mb_strtolower($deck['side_name']);

        $rows = $dbh->executeQuery("
            SELECT
              c.code,
              s.quantity
            FROM deckslot s
              JOIN card c ON s.card_id=c.id
            WHERE s.deck_id=?", [
            $deck['id'],
        ])->fetchAll();

        $cards = [];
        foreach ($rows as $row) {
            $cards[$row['code']] = $row['quantity'];
        }
        $deck['slots'] = $cards;

        $published_decklists = $dbh->executeQuery("
            SELECT
              d.id,
              d.uuid,
              d.name,
              d.prettyname,
              d.nbvotes,
              d.nbfavorites,
              d.nbcomments
            FROM decklist d
            WHERE d.parent_deck_id=?
              AND d.moderation_status IN (0,1)
            ORDER BY d.date_creation ASC",
            [
                $deck['id'],
            ]

        )->fetchAll();

        $parent_decklists = $dbh->executeQuery("
            SELECT
              d.id,
              d.uuid,
              d.name,
              d.prettyname,
              d.nbvotes,
              d.nbfavorites,
              d.nbcomments
            FROM decklist d, deck
            WHERE deck.id = ? AND d.id=deck.parent_decklist_id
              AND d.moderation_status IN (0,1)
            ORDER BY d.date_creation ASC",
            [
                $deck['id'],
            ]

        )->fetchAll();

        $tournaments = $dbh->executeQuery("
          SELECT
            t.id,
            t.description
          FROM tournament t
          ORDER BY t.description DESC"
        )->fetchAll();

        $problem = $deck['problem'];
        $deck['message'] = isset($problem) ? $judge->problem($deck) : '';

        $description = "An unpublished decklist by " . $deck["user_name"] . ".";

        $identity = $entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $deck["identity_code"]]);
        $image = "https://card-images.netrunnerdb.com/v1" . $identity->getMediumImagePath();

        return $this->render(

            '/Builder/deckview.html.twig',
            [
                'pagetitle'           => "Deckbuilder",
                'pagedescription'     => $description,
                'pageimage'           => $image,
                'deck'                => $deck,
                'published_decklists' => $published_decklists,
                'parent_decklists'    => $parent_decklists,
                'tournaments'         => $tournaments,
            ]

        );
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param DeckManager            $deckManager
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function listAction(EntityManagerInterface $entityManager, DeckManager $deckManager)
    {
        $user = $this->getUser();

        $decks = $deckManager->getByUser($user, false);

        $tournaments = $entityManager->getConnection()->executeQuery(
            "SELECT
               t.id,
               t.description
             FROM tournament t
             ORDER BY t.description DESC"
        )->fetchAll();

        $list_mwl = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);

        return $this->render(

            '/Builder/decks.html.twig',
            [
                'pagetitle'       => "My Decks",
                'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                'decks'           => $decks,
                'nbmax'           => $user->getMaxNbDecks(),
                'nbdecks'         => count($decks),
                'cannotcreate'    => $user->getMaxNbDecks() <= count($decks),
                'tournaments'     => $tournaments,
                'list_mwl'        => $list_mwl,
            ]

        );
    }

    /**
     * @param Decklist $decklist
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     *
     * @ParamConverter("decklist", class="AppBundle:Decklist", options={"mapping": {"decklist_uuid": "uuid"}})
     */
    public function copyAction(Decklist $decklist)
    {
        $content = [];
        foreach ($decklist->getSlots() as $slot) {
            $content[$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        $mwl = $decklist->getMwl();

        return $this->forward(
            'AppBundle:Builder:save',
            [
                'name'        => $decklist->getName(),
                'content'     => json_encode($content),
                'decklist_id' => $decklist->getId(),
                'mwl_code'    => $mwl instanceof Mwl ? $mwl->getCode() : null,
            ]
        );
    }

    /**
     * @param Deck $deck
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     *
     * @ParamConverter("deck", class="AppBundle:Deck", options={"mapping": {"deck_uuid": "uuid"}})
     */
    public function duplicateAction(Deck $deck)
    {
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $content = [];
        foreach ($deck->getSlots() as $slot) {
            $content[$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        $description = strlen($deck->getDescription()) > 0 ? ("Original deck notes:\n\n" . $deck->getDescription()) : '';

        $mwl = $deck->getMwl();
        if ($mwl instanceof Mwl) {
            $mwl = $mwl->getCode();
        }

        return $this->forward(
            'AppBundle:Builder:save',
            [
                'name'        => $deck->getName() . ' (copy)',
                'content'     => json_encode($content),
                'description' => $description,
                'deck_id'     => $deck->getParent() ? $deck->getParent()->getId() : null,
                'mwl_code'    => $mwl,
                'tags'        => $deck->getTags(),
            ]
        );
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param DeckManager            $deckManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function downloadallAction(EntityManagerInterface $entityManager, DeckManager $deckManager)
    {
        $user = $this->getUser();

        $decks = $deckManager->getByUser($user, false);

        $file = tempnam("tmp", "zip");
        $zip = new \ZipArchive();
        $res = $zip->open($file, \ZipArchive::OVERWRITE);
        if ($res === true) {
            foreach ($decks as $deck) {
                $content = [];
                foreach ($deck['cards'] as $slot) {
                    $card = $entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $slot['card_code']]);
                    if (!$card instanceof Card) {
                        continue;
                    }
                    $cardtitle = $card->getTitle();
                    $packname = $card->getPack()->getName();
                    if ($packname == 'Core Set') {
                        $packname = 'Core';
                    }
                    $qty = $slot['qty'];
                    $content[] = "$cardtitle ($packname) x$qty";
                }
                $filename = str_replace('/', ' ', $deck['name']) . '.txt';
                $zip->addFromString($filename, implode("\r\n", $content));
            }
            $zip->close();
        }
        $response = new Response();
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Length', filesize($file));
        $response->headers->set('Content-Disposition', 'attachment; filename="netrunnerdb.zip"');
        $response->setContent(file_get_contents($file));
        unlink($file);

        return $response;
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param DeckManager            $deckManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function uploadallAction(Request $request, EntityManagerInterface $entityManager, DeckManager $deckManager)
    {
        // time-consuming task
        ini_set('max_execution_time', 300);

        $uploadedFile = $request->files->get('uparchive');
        if (!isset($uploadedFile)) {
            return new Response('No file');
        }

        $filename = $uploadedFile->getPathname();

        if (function_exists("finfo_open")) {
            // return mime type ala mimetype extension
            $finfo = finfo_open(FILEINFO_MIME);

            // check to see if the mime-type is 'zip'
            if (substr(finfo_file($finfo, $filename), 0, 15) !== 'application/zip') {
                return new Response('Bad file');
            }
        }

        $zip = new \ZipArchive;
        $res = $zip->open($filename);
        if ($res === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $parse = $this->parseTextImport($zip->getFromIndex($i), $entityManager);

                $deck = new Deck();
                $deckManager->saveDeck($this->getUser(), $deck, null, $name, '', [], null, $parse['content'], null);
            }
        }
        $zip->close();

        $this->addFlash('notice', "DeckManager imported.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Deck $deck
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     *
     * @ParamConverter("deck", class="AppBundle:Deck", options={"mapping": {"deck_uuid": "uuid"}})
     */
    public function autosaveAction(Deck $deck, Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        if ($user->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $diff = (array) json_decode($request->get('diff'));
        if (count($diff) != 2) {
            throw new BadRequestHttpException("Wrong content " . $diff);
        }
        if (count((array) $diff[0]) || count((array) $diff[1])) {
            $change = new Deckchange();
            $change->setDeck($deck);
            $change->setVariation(json_encode($diff));
            $change->setSaved(false);
            $entityManager->persist($change);
            $entityManager->flush();

            return new Response($change->getDateCreation()->format('c'));
        }

        return new Response('');
    }
}

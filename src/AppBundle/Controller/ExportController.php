<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Deck;
use AppBundle\Service\DeckManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @package AppBundle\Controller
 */
class ExportController extends Controller
{
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    private function organizeCardsByType($deck)
    {
        $organizedCards = [];
        $cards = $deck->getSlots();

        foreach ($cards as $slot) {
            $card = $slot->getCard();
            $cardType = $card->getType()->getCode();
            if ($cardType === 'identity') continue;

            if (!isset($organizedCards[$cardType])) {
                $organizedCards[$cardType] = [];
            }

            $organizedCards[$cardType][] = [
                'name' => $card->getTitle(),
                'quantity' => $slot->getQuantity(),
                'influence' => $card->getFactionCost(),
                'faction_code' => $card->getFaction()->getCode()
            ];
        }

        foreach ($organizedCards as &$category) {
            usort($category, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
        }

        return $organizedCards;
    }

    private function fetchCorpAndRunnerDecks($deck_uuid, $second_deck_uuid)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $deck1 = $entityManager->getRepository('AppBundle:Deck')->findOneBy(['uuid' => $deck_uuid]);
        $deck2 = $entityManager->getRepository('AppBundle:Deck')->findOneBy(['uuid' => $second_deck_uuid]);

        if (!$deck1 || !$deck2) {
            throw $this->createNotFoundException('One or both decks not found.');
        }

        $user = $this->getUser();
        if ($deck1->getUser() !== $user || $deck2->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have access to one or both decks.');
        }

        $deck1Side = $deck1->getSide()->getCode();
        $deck2Side = $deck2->getSide()->getCode();
        if (!($deck1Side === 'corp' && $deck2Side === 'runner') &&
            !($deck1Side === 'runner' && $deck2Side === 'corp')) {
            throw new \InvalidArgumentException('One deck must be Corp and one must be Runner.');
        }
        $corpDeck = $deck1Side === 'corp' ? $deck1 : $deck2;
        $runnerDeck = $deck1Side === 'runner' ? $deck1 : $deck2;

        return [$corpDeck, $runnerDeck];
    }

    private function prepareTemplateData(Deck $runnerDeck, Deck $corpDeck)
    {
        $runnerIdentity = $runnerDeck->getIdentity();
        $runnerCards = $this->organizeCardsByType($runnerDeck);
        $corpIdentity = $corpDeck->getIdentity();
        $corpCards = $this->organizeCardsByType($corpDeck);

        return [
            'runner_deck' => $runnerDeck,
            'runner_identity' => $runnerIdentity,
            'runner_cards' => $runnerCards,
            'corp_deck' => $corpDeck,
            'corp_identity' => $corpIdentity,
            'corp_cards' => $corpCards
        ];
    }

    public function listComplementaryDecksAction($side, DeckManager $deckManager, EntityManagerInterface $entityManager){
        $user = $this->getUser();

        $all_decks = $deckManager->getByUser($user, false);
        $complementary_decks = [];

        foreach ($all_decks as $deck) {
            if ($deck['side'] === $side) {
                $deck['identity_image_path'] = $this->getParameter('card_image_url').$entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $deck['identity_code']])->getSmallImagePath();
                $complementary_decks[] = $deck;
            }
        }

        return new Response(json_encode($complementary_decks), 200);
    }

    public function tournamentSheetAction($deck_uuid, $second_deck_uuid)
    {
        try {
            $response = new Response();
            $response->setPrivate();
            $response->setMaxAge($this->getParameter('long_cache'));

            list($corpDeck, $runnerDeck) = $this->fetchCorpAndRunnerDecks($deck_uuid, $second_deck_uuid);

            $data = $this->prepareTemplateData($runnerDeck, $corpDeck);
            return $this->render('Export/tournament_sheet.html.twig', $data, $response);
        } catch (\Exception $e) {
            $this->logger->error('Tournament sheet error: ' . $e->getMessage());
            return new Response('Error generating tournament sheet: ' . $e->getMessage(), 500);
        }
    }
}
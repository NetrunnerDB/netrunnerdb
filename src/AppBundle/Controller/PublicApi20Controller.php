<?php

namespace AppBundle\Controller;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Decklist;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\FOSRestController;

class PublicApi20Controller extends FOSRestController
{
    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function prepareResponse(array $entities, Request $request, array $additionalTopLevelProperties = [])
    {
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        $dateUpdate = array_reduce($entities, function ($carry, TimestampableInterface $item) {
            if (!$carry || ($item->getDateUpdate() > $carry)) {
                return $item->getDateUpdate();
            } else {
                return $carry;
            }
        });

        $response->setLastModified($dateUpdate);
        if ($response->isNotModified($request)) {
            return $response;
        }

        $locale = $request->query->get('_locale');

        $content = $additionalTopLevelProperties;

        $content['data'] = array_map(function (NormalizableInterface $entity) {
            return $entity->normalize();
        }, $entities);

        $content['total'] = count($content['data']);
        $content['success'] = true;
        $content['version_number'] = '2.0';
        $content['last_updated'] = $dateUpdate ? $dateUpdate->format('c') : null;

        $response->setData($content);

        return $response;
    }

    private function prepareFailedResponse(string $msg)
    {
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->setPrivate();

        $content = [ 'version_number' => '2.0' ];
        $content['success'] = false;
        $content['msg'] = $msg;

        $response->setData($content);

        return $response;
    }

    /**
     * Get a type
     *
     * @ApiDoc(
     *  section="Type",
     *  resource=true,
     *  description="Get one type",
     *  parameters={
     *  },
     * )
     */
    public function typeAction(string $type_code, Request $request)
    {
        $type = $this->entityManager->getRepository('AppBundle:Type')->findOneBy(['code' => $type_code]);

        if (!$type) {
            throw $this->createNotFoundException();
        }

        return $this->prepareResponse([$type], $request);
    }

    /**
     * Get all the types
     *
     * @ApiDoc(
     *  section="Type",
     *  resource=true,
     *  description="Get all the types",
     *  parameters={
     *  },
     * )
     */
    public function typesAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Type')->findAll();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get a side
     *
     * @ApiDoc(
     *  section="Side",
     *  resource=true,
     *  description="Get one side",
     *  parameters={
     *  },
     * )
     */
    public function sideAction(string $side_code, Request $request)
    {
        $side = $this->entityManager->getRepository('AppBundle:Side')->findOneBy(['code' => $side_code]);

        if (!$side) {
            throw $this->createNotFoundException();
        }

        return $this->prepareResponse([$side], $request);
    }

    /**
     * Get all the sides
     *
     * @ApiDoc(
     *  section="Side",
     *  resource=true,
     *  description="Get all the sides",
     *  parameters={
     *  },
     * )
     */
    public function sidesAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Side')->findAll();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get a faction
     *
     * @ApiDoc(
     *  section="Faction",
     *  resource=true,
     *  description="Get one faction",
     *  parameters={
     *  },
     * )
     */
    public function factionAction(string $faction_code, Request $request)
    {
        $faction = $this->entityManager->getRepository('AppBundle:Faction')->findOneBy(['code' => $faction_code]);

        if (!$faction) {
            throw $this->createNotFoundException();
        }

        return $this->prepareResponse([$faction], $request);
    }

    /**
     * Get all the factions
     *
     * @ApiDoc(
     *  section="Faction",
     *  resource=true,
     *  description="Get all the factions",
     *  parameters={
     *  },
     * )
     */
    public function factionsAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Faction')->findAll();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get a cycle
     *
     * @ApiDoc(
     *  section="Cycle",
     *  resource=true,
     *  description="Get one cycle",
     *  parameters={
     *  },
     * )
     */
    public function cycleAction(string $cycle_code, Request $request)
    {
        $cycle = $this->entityManager->getRepository('AppBundle:Cycle')->findOneBy(['code' => $cycle_code]);

        if (!$cycle) {
            throw $this->createNotFoundException();
        }

        return $this->prepareResponse([$cycle], $request);
    }

    /**
     * Get all the cycles
     *
     * @ApiDoc(
     *  section="Cycle",
     *  resource=true,
     *  description="Get all the cycles",
     *  parameters={
     *  },
     * )
     */
    public function cyclesAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Cycle')->findAll();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get a pack
     *
     * @ApiDoc(
     *  section="Pack",
     *  resource=true,
     *  description="Get one pack",
     *  parameters={
     *  },
     * )
     */
    public function packAction(string $pack_code, Request $request)
    {
        $pack = $this->entityManager->getRepository('AppBundle:Pack')->findOneBy(['code' => $pack_code]);

        if (!$pack) {
            throw $this->createNotFoundException();
        }

        return $this->prepareResponse([$pack], $request);
    }

    /**
     * Get all the packs as an array of JSON objects.
     *
     * @ApiDoc(
     *  section="Pack",
     *  resource=true,
     *  description="Get all the packs",
     *  parameters={
     *  },
     * )
     */
    public function packsAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Pack')->findAll();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get a card
     *
     * @ApiDoc(
     *  section="Card",
     *  resource=true,
     *  description="Get one card",
     *  parameters={
     *  },
     * )
     */
    public function cardAction(string $card_code, Request $request)
    {
        $card = $this->entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $card_code]);

        if (!$card) {
            throw $this->createNotFoundException();
        }

        return $this->prepareResponse([$card], $request, ['imageUrlTemplate' => $request->getSchemeAndHttpHost() . '/card_image/large/{code}.jpg']);
    }

    /**
     * Get all the cards as an array of JSON objects.
     *
     * @ApiDoc(
     *  section="Card",
     *  resource=true,
     *  description="Get all the cards",
     *  parameters={
     *  },
     * )
     */
    public function cardsAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Card')->findAll();

        return $this->prepareResponse($data, $request, ['imageUrlTemplate' => $request->getSchemeAndHttpHost() . '/card_image/large/{code}.jpg']);
    }

    /**
     * Get a decklist
     *
     * @ApiDoc(
     *  section="Decklist",
     *  resource=true,
     *  description="Get one (published) decklist",
     *  parameters={
     *  },
     * )
     *
     * @ParamConverter("decklist", class="AppBundle:Decklist", options={"id" = "decklist_id"})
     */
    public function decklistAction(Decklist $decklist, Request $request)
    {
        return $this->prepareResponse([$decklist], $request);
    }

    /**
     * Get all the decklists for a date
     *
     * @ApiDoc(
     *  section="Decklist",
     *  resource=true,
     *  description="Get all the (published) decklists for a date",
     *  parameters={
     *  },
     * )
     */
    public function decklistsByDateAction(string $date, Request $request, EntityManagerInterface $entityManager)
    {
        $date_from = new \DateTime($date);
        $date_to = clone($date_from);
        $date_to->modify('+1 day');

        $date_today = new \DateTime();
        if ($date_today < $date_from) {
            return $this->prepareFailedResponse("Date is in the future");
        }

        $qb = $entityManager->createQueryBuilder()->select('d')->from('AppBundle:Decklist', 'd');
        $qb->where($qb->expr()->between('d.dateCreation', ':date_from', ':date_to'));
        $qb->setParameter('date_from', $date_from, Type::DATETIME);
        $qb->setParameter('date_to', $date_to, Type::DATETIME);

        $data = $qb->getQuery()->execute();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get a deck
     *
     * @ApiDoc(
     *  section="Deck",
     *  resource=true,
     *  description="Get one (private, shared) deck",
     *  parameters={
     *  },
     * )
     *
     * @ParamConverter("deck", class="AppBundle:Deck", options={"id" = "deck_id"})
     */
    public function deckAction(Deck $deck, Request $request)
    {
        if (!$deck->getUser()->getShareDecks()) {
            throw $this->createAccessDeniedException();
        }

        return $this->prepareResponse([$deck], $request);
    }

    /**
     * Get all prebuilts
     *
     * @ApiDoc(
     *  section="Prebuilt",
     *  resource=true,
     *  description="Get all the prebuilts",
     *  parameters={
     *  },
     * )
     */
    public function prebuiltsAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Prebuilt')->findAll();

        return $this->prepareResponse($data, $request);
    }

    /**
     * Get all MWL data
     *
     * @ApiDoc(
     *  section="MWL",
     *  resource=true,
     *  description="Get all the mwl data",
     *  parameters={
     *  },
     * )
     */
    public function mwlAction(Request $request)
    {
        $data = $this->entityManager->getRepository('AppBundle:Mwl')->findAll();

        return $this->prepareResponse($data, $request);
    }
}

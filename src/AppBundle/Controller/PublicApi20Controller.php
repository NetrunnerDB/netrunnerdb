<?php

namespace AppBundle\Controller;

use DateTime;
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
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

class PublicApi20Controller extends FOSRestController
{
    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;
    protected $cache;

    public function __construct(EntityManagerInterface $entityManager, AdapterInterface $cache)
    {
      $this->entityManager = $entityManager;
      $this->cache = $cache;
    }

    private function getSingleEntityFromCache(string $cachePrefix, $entityFunction, Request $request, array $additionalTopLevelProperties = []) {
      $cacheData = $this->cache->getItem($cachePrefix);
      $cacheDateUpdate = $this->cache->getItem($cachePrefix . '-date-update');
      $cacheCount = $this->cache->getItem($cachePrefix . '-count');

      if (!$cacheData->isHit()) {
        $entities = $entityFunction();
        $data = $entities ? $this->getEntityJson([$entities]) : [];
        $dateUpdate = $entities ? $this->getDateUpdateFromEntities([$entities]) : new DateTime();

        $cacheData->set($data);
        $cacheDateUpdate->set($dateUpdate);
        $cacheCount->set($entities ? 1 : 0);

        $this->cache->save($cacheData);
        $this->cache->save($cacheDateUpdate);
        $this->cache->save($cacheCount);
      }
      return $this->prepareResponseFromCache($cacheData->get(), $cacheCount->get(), $cacheDateUpdate->get(), $request, $additionalTopLevelProperties);
    }

    private function getFromCache(string $cachePrefix, $entityFunction, Request $request, array $additionalTopLevelProperties = []) {
      $cacheData = $this->cache->getItem($cachePrefix);
      $cacheDateUpdate = $this->cache->getItem($cachePrefix . '-date-update');
      $cacheCount = $this->cache->getItem($cachePrefix . '-count');

      if (!$cacheData->isHit()) {
        $entities = $entityFunction();
        $data = $this->getEntityJson($entities);
        $dateUpdate = $this->getDateUpdateFromEntities($entities);

        $cacheData->set($data);
        $cacheDateUpdate->set($dateUpdate);
        $cacheCount->set(count($entities));

        $this->cache->save($cacheData);
        $this->cache->save($cacheDateUpdate);
        $this->cache->save($cacheCount);
      }
      return $this->prepareResponseFromCache($cacheData->get(), $cacheCount->get(), $cacheDateUpdate->get(), $request, $additionalTopLevelProperties);
    }

    private function getDateUpdateFromEntities(array $entities) {
      return array_reduce($entities, function ($carry, TimestampableInterface $item) {
        if (!$carry || ($item->getDateUpdate() > $carry)) {
          return $item->getDateUpdate();
        } else {
          return $carry;
        }
      });
   }

    private function getEntityJson(array $entities) {
      return array_map(function (NormalizableInterface $entity) {
        return $entity->normalize();
      }, $entities);
    }

    private function prepareResponseFromCache($data, $count, $dateUpdate, Request $request, array $additionalTopLevelProperties = [])
    {
      $response = new JsonResponse();
      $response->headers->set('Access-Control-Allow-Origin', '*');
      $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
      $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $response->setPublic();
      $response->setMaxAge($this->getParameter('short_cache'));

      $response->setLastModified($dateUpdate);
      if ($response->isNotModified($request)) {
        return $response;
      }

      $locale = $request->query->get('_locale');

      $content = $additionalTopLevelProperties;

      $content['data'] = $data;
      $content['total'] = $count;
      $content['success'] = $count == 0 ? false : true;
      $content['version_number'] = '2.0';
      $content['last_updated'] = $dateUpdate ? $dateUpdate->format('c') : null;

      $response->setData($content);

      return $response;
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
      return $this->getSingleEntityFromCache("public-api-type-" . $type_code, function() use ($type_code) {
        return $this->entityManager->getRepository('AppBundle:Type')->findOneBy(['code' => $type_code]);
      }, $request);
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
      return $this->getFromCache("public-api-types", function() {
        return $this->entityManager->getRepository('AppBundle:Type')->findAll();
      }, $request);
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
      return $this->getSingleEntityFromCache("public-api-side-" . $side_code, function() use ($side_code) {
        return $this->entityManager->getRepository('AppBundle:Side')->findOneBy(['code' => $side_code]);
      }, $request);
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
      return $this->getFromCache("public-api-sides", function() {
        return $this->entityManager->getRepository('AppBundle:Side')->findAll();
      }, $request);
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
      return $this->getSingleEntityFromCache("public-api-faction-" . $faction_code, function() use ($faction_code) {
        return $this->entityManager->getRepository('AppBundle:Faction')->findOneBy(['code' => $faction_code]);
      }, $request);
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
      return $this->getFromCache("public-api-factions", function() {
        return $this->entityManager->getRepository('AppBundle:Faction')->findAll();
      }, $request);
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
      return $this->getSingleEntityFromCache("public-api-cycle-" . $cycle_code, function() use ($cycle_code) {
        return $this->entityManager->getRepository('AppBundle:Cycle')->findOneBy(['code' => $cycle_code]);
      }, $request);
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
      return $this->getFromCache("public-api-cycles", function() {
        return $this->entityManager->getRepository('AppBundle:Cycle')->findAll();
      }, $request);
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
      return $this->getSingleEntityFromCache("public-api-pack-" . $pack_code, function() use ($pack_code) {
        return $this->entityManager->getRepository('AppBundle:Pack')->findOneBy(['code' => $pack_code]);
      }, $request);
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
    public function packsAction(Request $request) {
      return $this->getFromCache("public-api-packs", function() {
        return $this->entityManager->getRepository('AppBundle:Pack')->findAll();
      }, $request);
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
      return $this->getSingleEntityFromCache("public-api-card-" . $card_code, function() use ($card_code) {
        return $this->entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $card_code]);
      }, $request, ['imageUrlTemplate' => rtrim($this->getParameter('card_image_url'), '/') . '/large/{code}.jpg']);
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
      return $this->getFromCache("public-api-cards", function() {
        return $this->entityManager->getRepository('AppBundle:Card')->findAll();
      }, $request, ['imageUrlTemplate' => rtrim($this->getParameter('card_image_url'), '/') . '/large/{code}.jpg']);
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
     */
    public function decklistByIdAction(int $decklist_id, Request $request)
    {
      return $this->getSingleEntityFromCache("public-api-decklist-" . $decklist_id, function() use ($decklist_id) {
        return $this->entityManager->getRepository('AppBundle:Decklist')->findOneBy(['id' => $decklist_id]);
      }, $request);
    }

    /**
     * Get a decklist by UUID
     *
     * @ApiDoc(
     *  section="Decklist",
     *  resource=true,
     *  description="Get one (published) decklist",
     *  parameters={
     *  },
     * )
     *
     */
    public function decklistByUuidAction(string $decklist_uuid, Request $request)
    {
      return $this->getSingleEntityFromCache("public-api-decklist-" . $decklist_uuid, function() use ($decklist_uuid) {
        return $this->entityManager->getRepository('AppBundle:Decklist')->findOneBy(['uuid' => $decklist_uuid]);
      }, $request);
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
        // Not caching because this appears to be used only dozens of times per day.
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
    public function deckByIdAction(Deck $deck, Request $request)
    {
        // Not currently caching because this is currently very rarely used.
        if (!$deck->getUser()->getShareDecks()) {
          throw $this->createAccessDeniedException();
        }

        return $this->prepareResponse([$deck], $request);
    }

    /**
     * Get a deck by UUID
     *
     * @ApiDoc(
     *  section="Deck",
     *  resource=true,
     *  description="Get one (private, shared) deck",
     *  parameters={
     *  },
     * )
     *
     * @ParamConverter("deck", class="AppBundle:Deck", options={"mapping": {"deck_uuid": "uuid"}})
     */
    public function deckByUuidAction(Deck $deck, Request $request)
    {
        // Not currently caching because this is currently very rarely used.
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
      return $this->getFromCache("public-api-prebuilts", function() {
        return $this->entityManager->getRepository('AppBundle:Prebuilt')->findAll();
      }, $request);
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
      return $this->getFromCache("public-api-mwl", function() {
        return $this->entityManager->getRepository('AppBundle:Mwl')->findAll();
      }, $request);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Cycle;
use AppBundle\Entity\Mwl;
use AppBundle\Entity\Pack;
use AppBundle\Entity\Rotation;
use AppBundle\Entity\Type;
use AppBundle\Service\CardsData;
use AppBundle\Service\Illustrators;
use AppBundle\Service\RotationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    const SORT_OPTIONS = array(
        'name'         => 'Name',
        'set'          => 'Set Name',
        'release-date' => 'Release Date',
        'faction'      => 'Faction',
        'type'         => 'Type',
        'cost'         => 'Cost',
        'strength'     => 'Strength'
    );

    const VIEW_OPTIONS = array(
        'list'    => 'a Checklist',
        'text'    => 'Text only',
        'full'    => 'Full Cards',
        'images'  => 'Images only',
        'rulings' => 'Rulings only',
        'short'   => 'Names only'
    );
    /**
     * @param EntityManagerInterface $entityManager
     * @param CardsData              $cardsData
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function formAction(EntityManagerInterface $entityManager, CardsData $cardsData, Illustrators $illustrators)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        $dbh = $entityManager->getConnection();

        $list_packs = $entityManager->getRepository('AppBundle:Pack')->findBy([], [
            "dateRelease" => "ASC",
            "position"    => "ASC",
        ]);
        $packs = [];
        foreach ($list_packs as $pack) {
            $packs [] = [
                "name" => $pack->getName(),
                "code" => $pack->getCode(),
            ];
        }

        $list_cycles = $entityManager->getRepository('AppBundle:Cycle')->findBy([], [
            "position" => "ASC",
        ]);
        $cycles = [];
        foreach ($list_cycles as $cycle) {
            $cycles [] = [
                "name" => $cycle->getName(),
                "code" => $cycle->getCode(),
            ];
        }

        $list_types = $entityManager->getRepository('AppBundle:Type')->findBy([
            "isSubtype" => false,
        ], [
            "name" => "ASC",
        ]);
        $types = array_map(function (Type $type) {
            return $type->getName();
        }, $list_types);

        $list_keywords = $dbh->executeQuery("SELECT DISTINCT c.keywords FROM card c WHERE c.keywords != ''")->fetchAll();
        $keywords = [];
        foreach ($list_keywords as $keyword) {
            $subs = explode(' - ', $keyword ["keywords"]);
            foreach ($subs as $sub) {
                $keywords [$sub] = 1;
            }
        }
        $keywords = array_keys($keywords);
        sort($keywords);

        $illustrator_map = array();
        $list_illustrators = $dbh->executeQuery("SELECT DISTINCT c.illustrator FROM card c WHERE c.illustrator != '' ORDER BY c.illustrator")->fetchAll();
        foreach ($list_illustrators as $illustrator) {
            $illustrator_map[$illustrator['illustrator']] = 1;
            foreach ($illustrators->split($illustrator['illustrator']) as $split) {
                $illustrator_map[$split] = 1;
            }
        }
        ksort($illustrator_map);

        $banlists = $entityManager->getRepository(Mwl::class)->findBy([], ['dateStart' => 'DESC']);
        $rotations = $entityManager->getRepository(Rotation::class)->findBy([], ['dateStart' => 'DESC']);

     return $this->render('/Search/advanced-search.html.twig', [
            "pagetitle"       => "Card Search",
            "pagedescription" => "Find all the cards of the game, easily searchable.",
            "packs"           => $packs,
            "cycles"          => $cycles,
            "types"           => $types,
            "keywords"        => $keywords,
            "illustrators"    => array_keys($illustrator_map),
            "rotations"       => $rotations,
            "banlists"        => $banlists,
            "sort"            => "name",
            "view"            => "list",
            "sort_options"    => self::SORT_OPTIONS,
            "view_options"    => self::VIEW_OPTIONS,
        ], $response);
    }

    /**
     * @param string                 $card_code
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function zoomAction(string $card_code, Request $request, EntityManagerInterface $entityManager)
    {
        $card = $entityManager->getRepository('AppBundle:Card')->findOneBy(["code" => $card_code]);
        if (!$card instanceof Card) {
            throw $this->createNotFoundException();
        }
        $meta = $card->getTitle() . ", a " . $card->getFaction()->getName() . " " . $card->getType()->getName() . " card for Android: Netrunner from the set " . $card->getPack()->getName() . " published by Fantasy Flight Games.";

        return $this->forward(
            'AppBundle:Search:display',
            [
                '_route'           => $request->attributes->get('_route'),
                '_route_params'    => $request->attributes->get('_route_params'),
                'q'                => $card->getCode(),
                'view'             => 'card',
                'sort'             => 'set',
                'title'            => $card->getTitle(),
                'meta'             => $meta,
                'locale'           => $request->getLocale(),
            ]
        );
    }

    /**
     * @param string                 $pack_code
     * @param string                 $view
     * @param string                 $sort
     * @param int                    $page
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function listAction(string $pack_code, string $view, string $sort, int $page, Request $request, EntityManagerInterface $entityManager)
    {
        $pack = $entityManager->getRepository('AppBundle:Pack')->findOneBy(["code" => $pack_code]);
        if (!$pack instanceof Pack) {
            throw $this->createNotFoundException();
        }
        $meta = $pack->getName() . ", a set of cards for Android: Netrunner"
            . ($pack->getDateRelease() ? " published on " . $pack->getDateRelease()->format('Y/m/d') : "")
            . " by Fantasy Flight Games.";

        // Find previous and next packs for navigation.
        $em = $entityManager->getRepository('AppBundle:Pack');
        $prev_pack = $em->createQueryBuilder('p')
            ->where('p.dateRelease < :date_release')
            ->setParameter('date_release', $pack->getDateRelease())
            ->orderBy('p.dateRelease', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $next_pack = $em->createQueryBuilder('p')
            ->where('p.dateRelease > :date_release')
            ->setParameter('date_release', $pack->getDateRelease())
            ->orderBy('p.dateRelease', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $this->forward(
            'AppBundle:Search:display',
            [
                '_route'        => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q'             => 'e:' . $pack_code,
                'view'          => $view,
                'sort'          => $sort,
                'page'          => $page,
                'title'         => $pack->getName(),
                'meta'          => $meta,
                'locale'        => $request->getLocale(),
                'currentPack'   => $pack,
                'prevPack'      => $prev_pack,
                'nextPack'      => $next_pack
            ]
        );
    }

    /**
     * @param string                 $cycle_code
     * @param string                 $view
     * @param string                 $sort
     * @param int                    $page
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function cycleAction(string $cycle_code, string $view, string $sort, int $page, Request $request, EntityManagerInterface $entityManager)
    {
        $cycle = $entityManager->getRepository('AppBundle:Cycle')->findOneBy(["code" => $cycle_code]);
        if (!$cycle instanceof Cycle) {
            throw $this->createNotFoundException();
        }
        $meta = $cycle->getName() . ", a cycle of datapack for Android: Netrunner published by Fantasy Flight Games.";

        return $this->forward(
            'AppBundle:Search:display',
            [
                '_route'        => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q'             => 'c:' . $cycle->getPosition(),
                'view'          => $view,
                'sort'          => $sort,
                'page'          => $page,
                'title'         => $cycle->getName(),
                'meta'          => $meta,
                'locale'        => $request->getLocale(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function processAction(Request $request)
    {
        $view = $request->query->get('view') ?: 'list';
        $sort = $request->query->get('sort') ?: 'name';
        $locale = $request->query->get('_locale') ?: $request->getLocale();

        $operators = [":", "!", "<", ">"];

        $params = [];
        if ($request->query->get('q') != "") {
            $params[] = $request->query->get('q');
        }
        $keys = ["e", "t", "f", "s", "x", "p", "o", "n", "d", "r", "i", "l", "y", "a", "u", "b", "z"];
        foreach ($keys as $key) {
            $val = $request->query->get($key);
            if (isset($val) && $val != "") {
                if (is_array($val)) {
                    $params[] = $key . ":" . implode("|", array_map(function ($s) {
                        return strstr($s, " ") !== false ? "\"$s\"" : $s;
                    }, $val));
                } else {
                    if (strstr($val, " ") != false) {
                        $val = "\"$val\"";
                    }
                    $op = $request->query->get($key . "o");
                    if (!in_array($op, $operators)) {
                        $op = ":";
                    }
                    if ($key == "r") {
                        $op = "";
                    }
                    $params[] = "$key$op$val";
                }
            }
        }
        $find = ['q' => implode(" ", $params)];
        if ($sort != "name") {
            $find['sort'] = $sort;
        }
        if ($view != "list") {
            $find['view'] = $view;
        }
        if ($locale != "en") {
            $find['_locale'] = $locale;
        }

        return $this->redirect($this->generateUrl('cards_find') . '?' . http_build_query($find));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param CardsData              $cardsData
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function findAction(Request $request, EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $q = $request->query->get('q', '');
        $page = $request->query->get('page') ?: 1;
        $view = $request->query->get('view') ?: 'list';
        $sort = $request->query->get('sort') ?: 'name';
        $locale = $request->query->get('_locale') ?: 'en';

        $request->setLocale($locale);

        // we may be able to redirect to a better url if the search is on a single set
        $conditions = $cardsData->syntax($q);
        if (count($conditions) == 1 && count($conditions[0]) == 3 && $conditions[0][1] == ":") {
            if ($conditions[0][0] == "e") {
                $url = $this->generateUrl('cards_list', ['pack_code' => $conditions[0][2], 'view' => $view, 'sort' => $sort, 'page' => $page, '_locale' => $request->getLocale()]);

                return $this->redirect($url);
            }
            if ($conditions[0][0] == "c") {
                $cycle_position = $conditions[0][2];
                $cycle = $entityManager->getRepository('AppBundle:Cycle')->findOneBy(['position' => $cycle_position]);
                if ($cycle instanceof Cycle) {
                    $url = $this->generateUrl('cards_cycle', ['cycle_code' => $cycle->getCode(), 'view' => $view, 'sort' => $sort, 'page' => $page, '_locale' => $request->getLocale()]);

                    return $this->redirect($url);
                }
            }
        }

        return $this->forward(
            'AppBundle:Search:display',
            [
                'q'             => $q,
                'view'          => $view,
                'sort'          => $sort,
                'page'          => $page,
                'locale'        => $locale,
                '_route'        => $request->get('_route'),
                '_route_params' => $request->get('_route_params'),
            ]
        );
    }

    /**
     * @param string                 $q
     * @param string                 $view
     * @param string                 $sort
     * @param int                    $page
     * @param string                 $title
     * @param string                 $meta
     * @param string|null            $locale
     * @param array|null             $locales
     * @param Pack|null              $currentPack
     * @param Pack|null              $prevPack
     * @param Pack|null              $nextPack
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param CardsData              $cardsData
     * @return Response
     */
    public function displayAction(
        string $q,
        string $view = "card",
        string $sort,
        int $page = 1,
        string $title = "",
        string $meta = "",
        string $locale = null,
        array $locales = null,
        Pack $currentPack = null,
        Pack $prevPack = null,
        Pack $nextPack = null,
        Request $request,
        EntityManagerInterface $entityManager,
        CardsData $cardsData
    ) {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        static $availability = [];

        if ($locale !== null) {
            $request->setLocale($locale);
        } else {
            $locale = $request->getLocale();
        }

        $cards = [];
        $first = 0;
        $last = 0;
        $pagination = '';

        $pagesizes = [
            'list'    => 200,
            'text'    => 200,
            'full'    => 20,
            'images'  => 20,
            'short'   => 1000,
            'rulings' => 200,
            'zoom'    => 1,
        ];

        $synonyms = [
            'spoiler' => 'text',
            'card'    => 'full',
            'scan'    => 'images',
        ];

        if (isset($synonyms[$view])) {
            $view = $synonyms[$view];
        }
        if (!isset($pagesizes[$view])) {
            $view = 'list';
        }

        $conditions = $cardsData->syntax($q);

        $cardsData->validateConditions($conditions);

        $card = null;
        $currentRotationCycles = [];

        $rows = $cardsData->get_search_rows($conditions, $sort, $locale);

        // If there are no results, and no specific criteria were searched, try again but force acronyms
        if (!$rows && !array_filter($conditions, function($c) {return $c[0] != "_";})) {
            $capsConditions = array_map(function($c) {return ["_", $c[1], strtoupper($c[2])];}, $conditions);
            $rows = $cardsData->get_search_rows($capsConditions, $sort, $locale);

            // If there are still no results, try again but with aliases
            if ($rows) {
                $conditions = $capsConditions;
            }
            else {
                $cardsData->unaliasCardNames($conditions);
                $rows = $cardsData->get_search_rows($conditions, $sort, $locale);
            }
        }

        // Reconstruct the correct search string for display
        $q = $cardsData->buildQueryFromConditions($conditions);

        $rows = $cardsData->select_only_latest_cards($rows);
        if ($rows) {
            if (count($rows) == 1) {
                $view = 'zoom';
            }

            if ($title == "") {
                if (count($conditions) == 1 && count($conditions[0]) == 3 && $conditions[0][1] == ":") {
                    if ($conditions[0][0] == "e") {
                        $pack = $entityManager->getRepository('AppBundle:Pack')->findOneBy(["code" => $conditions[0][2]]);
                        if ($pack instanceof Pack) {
                            $title = $pack->getName();
                        }
                    }
                    if ($conditions[0][0] == "c") {
                        $cycle = $entityManager->getRepository('AppBundle:Cycle')->findOneBy(["code" => $conditions[0][2]]);
                        if ($cycle instanceof Cycle) {
                            $title = $cycle->getName();
                        }
                    }
                }
            }

            //  calculate pagination
            $nb_per_page = $pagesizes[$view];
            $first = $nb_per_page * ($page - 1);
            if ($first > count($rows)) {
                $first = 0;
            }
            $last = $first + $nb_per_page;

            $versions = null;

            if ($view == "zoom") {
                // Other versions of a given card
                $versions = $cardsData->get_versions();
            }

            // data to pass to the view
            for ($rowindex = $first; $rowindex < $last && $rowindex < count($rows); $rowindex++) {
                /** @var Card $card */
                $card = $rows[$rowindex];
                $pack = $card->getPack();
                $cardinfo = $cardsData->getCardInfo($card, $locale);
                if (empty($availability[$pack->getCode()])) {
                    $availability[$pack->getCode()] = false;
                    if (
                        // Draft and Terminal Directive Campaign
                        $pack->getCode() != "draft" && $pack->getCode() != "tdc" &&
                        // Cards before release date
                        $pack->getDateRelease() && $pack->getDateRelease() <= new \DateTime()
                    ) {
                        $availability[$pack->getCode()] = true;
                    }
                }
                $cardinfo['available'] = $availability[$pack->getCode()];

                if ($view == "zoom") {
                    $cardVersions = $versions[$card->getTitle()];

                    $rotationService = new RotationService($entityManager);
                    $currentRotation = $rotationService->findCurrentRotation();
                    foreach($currentRotation->getRotated()->toArray() as $cycle) {
                        $currentRotationCycles[$cycle->getCode()] = true;
                    }
                    $cardinfo['versions'] = [];
                    $standard_legal = true;
                    $all_versions_rotated = true;

                    // Startup legality is currently hard-coded since the DB doesn't know anything about it.
                    $startupCycles = ['ashes' => true, 'system-gateway' => true, 'system-update-2021' => true, 'borealis' => true];
                    $startup_legal = false;

                    $rotated_count = 0;

                    foreach ($cardVersions as $version) {
                        $v = $cardsData->getCardInfo($version, $locale);
                        $cardinfo['versions'][] = $v;
                        // The 2 tutorial-only identity cards are invalid for startup and standard formats.
                        if ($v['code'] == '30077' || $v['code'] == '30076') {
                            $standard_legal = false;
                            $startup_legal = false;
                            continue;
                        }
                        // Draft and terminal directive campaign cards are not legal in standard.
                        if ($v['cycle_code'] == 'draft' || $v['pack_code'] == 'tdc') {
                            $standard_legal = false;
                        }
                        // Count the card's occurence in the rotated cycle(s)
                        if (array_key_exists($v['cycle_code'], $currentRotationCycles)) {
                            ++$rotated_count;
                        }
                        // Any printing of this card in a valid Startup cycle means the card is Startup legal.
                        if (array_key_exists($v['cycle_code'], $startupCycles)) {
                          $startup_legal = true;
                        }
                    }

                    // If any version of the card is not in a rotated cycle, the card is considered legal.
                    $all_versions_rotated = $rotated_count == count($cardinfo['versions']);

                    $cardinfo['reviews'] = $cardsData->get_reviews($cardVersions);
                    $cardinfo['rulings'] = $cardsData->get_rulings($cardVersions);
                    $cardinfo['mwl_info'] = $cardsData->get_mwl_info($cardVersions);
                    $cardinfo['startup_legality'] = $startup_legal ? 'legal' : 'banned';

                    if ($standard_legal) {
                        $cardinfo['standard_legality'] = $all_versions_rotated ? 'rotated' : 'legal';
                    } else {
                        $cardinfo['standard_legality'] = 'banned';
                    }
                }
                if ($view == "rulings") {
                    $cardinfo['rulings'] = $cardsData->get_rulings(array($card));
                }
                $cards[] = $cardinfo;
            }

            $first += 1;

            // if we have maps we display a navigation/pagination band
            if (count($rows) && $card instanceof Card) {
                if (count($rows) == 1) {
                    $pagination = $this->setnavigation($card, $locale, $entityManager);
                } else {
                    $currentPackUrl = $currentPack ? $this->generateUrl('cards_list', ['pack_code' => $currentPack->getCode(), "_locale" => $locale])  : '';
                    $currentPackName = $currentPack ? $currentPack->getName() : "";
                    $prevPackUrl = $prevPack ? $this->generateUrl('cards_list', ['pack_code' => $prevPack->getCode(), "_locale" => $locale])  : '';
                    $prevPackName = $prevPack ? $prevPack->getName() : "";
                    $nextPackUrl = $nextPack ? $this->generateUrl('cards_list', ['pack_code' => $nextPack->getCode(), "_locale" => $locale])  : '';
                    $nextPackName = $nextPack ? $nextPack->getName() : "";
                    $pagination = $this->pagination($nb_per_page, count($rows), $first, $q, $view, $sort, $locale, $currentPackUrl, $currentPackName, $prevPackUrl, $prevPackName, $nextPackUrl, $nextPackName);
                }
            }

            // if we are in "short" view, we break the list by sorting
            if (count($cards) && $view == "short") {
                $sortfields = [
                    'set'      => 'pack_name',
                    'name'     => 'title',
                    'faction'  => 'faction',
                    'type'     => 'type',
                    'cost'     => 'cost',
                    'strength' => 'strength',
                ];

                $brokenlist = [];
                for ($i = 0; $i < count($cards); $i++) {
                    $val = $cards[$i][$sortfields[$sort]];
                    if ($sort == "name") {
                        $val = substr($val, 0, 1);
                    }
                    if (!isset($brokenlist[$val])) {
                        $brokenlist[$val] = [];
                    }
                    array_push($brokenlist[$val], $cards[$i]);
                }
                $cards = $brokenlist;
            }
        }

        $searchbar = $this->renderView('/Search/searchbar.html.twig', [
            "q"            => $q,
            "view"         => $view,
            "sort"         => $sort,
            "sort_options" => self::SORT_OPTIONS,
            "view_options" => self::VIEW_OPTIONS,
        ]);

        if (empty($title)) {
            $title = $q;
        }

        if ($view == "zoom") {
            $card = $cards[0];
        }

        // be careful if $s="short", $cards is an array with 2 levels instead of just 1
        return $this->render('/Search/display-' . $view . '.html.twig', [
            "view"            => $view,
            "sort"            => $sort,
            "card"            => $card,
            "cards"           => $cards,
            "first"           => $first,
            "last"            => $last,
            "searchbar"       => $searchbar,
            "pagination"      => $pagination,
            "pagetitle"       => $title,
            "metadescription" => $meta,
            "locales"         => $locales,
            "currentRotationCycles" => $currentRotationCycles,
        ], $response);
    }

    public function setsAction(EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        return $this->render('/Search/display-set.html.twig', [
            "data" => $cardsData->allsetsdata(),
        ], $response);
    }

    /**
     * @param Card                   $card
     * @param string                 $locale
     * @param EntityManagerInterface $entityManager
     * @return string
     */
    public function setnavigation(Card $card, string $locale, EntityManagerInterface $entityManager)
    {
        $em = $entityManager->getRepository('AppBundle:Card');
        $prev = $em->createQueryBuilder('c')
            ->andWhere('c.pack = :pack')
            ->andWhere('c.position < :position')
            ->setParameter('pack', $card->getPack())
            ->setParameter('position', $card->getPosition())
            ->orderBy('c.position', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $next = $em->createQueryBuilder('c')
            ->andWhere('c.pack = :pack')
            ->andWhere('c.position > :position')
            ->setParameter('pack', $card->getPack())
            ->setParameter('position', $card->getPosition())
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $this->renderView('/Search/setnavigation.html.twig', [
            "prevtitle" => $prev instanceof Card ? $prev->getTitle() : "",
            "prevhref"  => $prev instanceof Card ? $this->generateUrl('cards_zoom', ['card_code' => $prev->getCode(), "_locale" => $locale]) : "",
            "nexttitle" => $next instanceof Card ? $next->getTitle() : "",
            "nexthref"  => $next instanceof Card ? $this->generateUrl('cards_zoom', ['card_code' => $next->getCode(), "_locale" => $locale]) : "",
            "settitle"  => $card->getPack()->getName(),
            "sethref"   => $this->generateUrl('cards_list', ['pack_code' => $card->getPack()->getCode(), "_locale" => $locale]),
            "_locale"   => $locale,
        ]);
    }

    /**
     * @param string|null $q
     * @param string      $v
     * @param string      $s
     * @param int         $ps
     * @param int         $pi
     * @param int         $total
     * @param string      $locale
     * @return string
     */
    public function paginationItem(string $q = null, string $v, string $s, int $ps, int $pi, int $total, string $locale)
    {
        return $this->renderView('/Search/paginationitem.html.twig', [
            "href" => $q == null ? "" : $this->generateUrl('cards_find', ['q' => $q, 'view' => $v, 'sort' => $s, 'page' => $pi, '_locale' => $locale]),
            "ps"   => $ps,
            "pi"   => $pi,
            "s"    => $ps * ($pi - 1) + 1,
            "e"    => min($ps * $pi, $total),
        ]);
    }

    /**
     * @param int    $pagesize
     * @param int    $total
     * @param int    $current
     * @param string $q
     * @param string $view
     * @param string $sort
     * @param string $locale
     * @param string $currentPackQuery
     * @param string $currentPackName
     * @param string $prevPackQuery
     * @param string $prevPackName
     * @param string $nextPackQuery
     * @param string $nextPackName
     * @return string
     */
    public function pagination(int $pagesize, int $total, int $current, string $q, string $view, string $sort, string $locale, string $currentPackQuery = null, $currentPackName = null, string $prevPackQuery = null, $prevPackName = null, string $nextPackQuery = null, $nextPackName = null)
    {
        if ($total < $pagesize) {
            $pagesize = $total;
        }

        $pagecount = ceil($total / $pagesize);
        $pageindex = ceil($current / $pagesize); #1-based

        $first = "";
        if ($pageindex > 2) {
            $first = $this->paginationItem($q, $view, $sort, $pagesize, 1, $total, $locale);
        }

        $prev = "";
        if ($pageindex > 1) {
            $prev = $this->paginationItem($q, $view, $sort, $pagesize, $pageindex - 1, $total, $locale);
        }

        $current = $this->paginationItem(null, $view, $sort, $pagesize, $pageindex, $total, $locale);

        $next = "";
        if ($pageindex < $pagecount) {
            $next = $this->paginationItem($q, $view, $sort, $pagesize, $pageindex + 1, $total, $locale);
        }

        $last = "";
        if ($pageindex < $pagecount - 1) {
            $last = $this->paginationItem($q, $view, $sort, $pagesize, $pagecount, $total, $locale);
        }

        return $this->renderView('/Search/pagination.html.twig', [
            "first"            => $first,
            "prev"             => $prev,
            "current"          => $current,
            "next"             => $next,
            "last"             => $last,
            "count"            => $total,
            "ellipsisbefore"   => $pageindex > 3,
            "ellipsisafter"    => $pageindex < $pagecount - 2,
            "currentPackQuery" => $currentPackQuery,
            "currentPackName"  => $currentPackName,
            "prevPackQuery"    => $prevPackQuery,
            "prevPackName"     => $prevPackName,
            "nextPackQuery"    => $nextPackQuery,
            "nextPackName"     => $nextPackName
        ]);
    }
}

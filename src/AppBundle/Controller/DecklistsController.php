<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Cycle;
use AppBundle\Entity\Rotation;
use AppBundle\Service\CardsData;
use AppBundle\Service\DecklistManager;
use AppBundle\Service\DiffService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Decklist;
use Symfony\Component\HttpFoundation\Request;

class DecklistsController extends Controller
{

    /**
     * @param string                 $type
     * @param int                    $page
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param DecklistManager        $decklistManager
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listAction(string $type, int $page = 1, Request $request, EntityManagerInterface $entityManager, DecklistManager $decklistManager, CardsData $cardsData)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        $limit = 30;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        $header = '';

        switch ($type) {
            case 'find':
                $result = $decklistManager->find($start, $limit, $request, $cardsData);
                $pagetitle = "Decklist search results";
                $pagedescription = "View the results of a decklist search.";
                $header = $this->searchForm($request, $entityManager, $cardsData);
                break;
            case 'favorites':
                $response->setPrivate();
                $user = $this->getUser();
                if (!$user) {
                    $result = ['decklists' => [], 'count' => 0];
                } else {
                    $result = $decklistManager->favorites($user->getId(), $start, $limit);
                }
                $pagetitle = "Favorite Decklists";
                $pagedescription = "View your favourited decklists.";
                break;
            case 'mine':
                $response->setPrivate();
                $user = $this->getUser();
                if (!$user) {
                    $result = ['decklists' => [], 'count' => 0];
                } else {
                    $result = $decklistManager->by_author($user->getId(), $start, $limit);
                }
                $pagetitle = "My Decklists";
                $pagedescription = "View your own published decklists.";
                break;
            case 'recent':
                $result = $decklistManager->recent($start, $limit);
                $pagetitle = "Recent Decklists";
                $pagedescription = "View recently published decklists.";
                break;
            case 'dotw':
                $result = $decklistManager->dotw($start, $limit);
                $pagetitle = "Decklist of the week";
                $pagedescription = "View the decklists of the week.";
                break;
            case 'halloffame':
                $result = $decklistManager->halloffame($start, $limit);
                $pagetitle = "Hall of Fame";
                $pagedescription = "View the most loved decklists from all of NetrunnerDB history.";
                break;
            case 'hottopics':
                $result = $decklistManager->hottopics($start, $limit);
                $pagetitle = "Hot Topics";
                $pagedescription = "View decklists currently receiving attention.";
                break;
            case 'tournament':
                $result = $decklistManager->tournaments($start, $limit);
                $pagetitle = "Tournaments";
                $pagedescription = "View decklists that placed in tournaments.";
                break;
            case 'trashed':
                $this->denyAccessUnlessGranted('ROLE_MODERATOR');
                $result = $decklistManager->trashed($start, $limit);
                $pagetitle = "Trashed decklists";
                break;
            case 'restored':
                $this->denyAccessUnlessGranted('ROLE_MODERATOR');
                $result = $decklistManager->restored($start, $limit);
                $pagetitle = "Restored decklists";
                break;
            case 'popular':
            default:
                $result = $decklistManager->popular($start, $limit);
                $pagetitle = "Popular Decklists";
                $pagedescription = "View popular recently published decklists.";
                break;
        }

        $decklists = $result['decklists'];
        $maxcount = $result['count'];

        $dbh = $entityManager->getConnection();
        $factions = $dbh->executeQuery(
            "SELECT
                f.name,
                f.code
                FROM faction f
                ORDER BY f.side_id ASC, f.name ASC"
        )->fetchAll();

        $packs = $dbh->executeQuery(
            "SELECT
                p.name,
                p.code
                FROM pack p
                WHERE p.date_release IS NOT NULL
                ORDER BY p.date_release DESC
                LIMIT 0,5"
        )->fetchAll();

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $params = $request->query->all();
        $params['type'] = $type;

        $pages = [];
        for ($page = 1; $page <= $nbpages; $page++) {
            $pages[] = [
                "numero"  => $page,
                "url"     => $this->generateUrl($route, $params + [
                        "page" => $page,
                    ]),
                "current" => $page == $currpage,
            ];
        }

        return $this->render('/Decklist/decklists.html.twig', [
            'pagetitle'       => $pagetitle,
            'pagedescription' => $pagedescription ? $pagedescription : "Browse the collection of thousands of premade decks.",
            'decklists'       => $decklists,
            'packs'           => $packs,
            'factions'        => $factions,
            'url'             => $request
                ->getRequestUri(),
            'header'          => $header,
            'route'           => $route,
            'pages'           => $pages,
            'type'            => $type,
            'prevurl'         => $currpage == 1 ? null : $this->generateUrl($route, $params + [
                    "page" => $prevpage,
                ]),
            'nexturl'         => $currpage == $nbpages ? null : $this->generateUrl($route, $params + [
                    "page" => $nextpage,
                ]),
        ], $response);
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function searchAction(Request $request, EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        $dbh = $entityManager->getConnection();
        $factions = $dbh->executeQuery(
            "SELECT
                f.name,
                f.code
                FROM faction f
                ORDER BY f.side_id ASC, f.name ASC"
        )->fetchAll();

        $cycles_and_packs = $cardsData->getCyclesAndPacks();

        $list_mwl = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);
        $list_rotations = $entityManager->getRepository(Rotation::class)->findBy([], ['dateStart' => 'DESC']);

        return $this->render('/Search/search.html.twig', [
            'pagetitle' => 'Decklist Search',
            'pagedescription' => 'Search for published Netrunner decklists.',
            'url'       => $request
                ->getRequestUri(),
            'factions'  => $factions,
            'form'      => $this->renderView(
                '/Search/form.html.twig',
                [
                    'cycles_and_packs' => $cycles_and_packs,
                    'author'           => '',
                    'title'            => '',
                    'list_mwl'         => $list_mwl,
                    'mwl_code'         => '',
                    'list_rotations'   => $list_rotations,
                    'rotation_id'      => '',
                    'is_legal'         => '',
                ]
            ),
        ], $response);
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    private function searchForm(Request $request, EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $dbh = $entityManager->getConnection();

        $cards_code = $request->query->get('cards');
        $faction_code = str_replace('-', '_', strtolower(filter_var($request->query->get('faction'), FILTER_SANITIZE_STRING)));
        $author_name = filter_var($request->query->get('author'), FILTER_SANITIZE_STRING);
        $decklist_title = filter_var($request->query->get('title'), FILTER_SANITIZE_STRING);
        $sort = $request->query->get('sort');
        $packs = $request->query->get('packs');
        $mwl_code = $request->query->get('mwl_code');
        $rotation_id = $request->query->get('rotation_id');
        $is_legal = $request->query->get('is_legal');

        $cycles_and_packs = $cardsData->getCyclesAndPacks(is_array($packs) ? $packs : []);

        $list_mwl = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);
        $list_rotations = $entityManager->getRepository(Rotation::class)->findBy([], ['dateStart' => 'DESC']);

        $params = [
            'cycles_and_packs' => $cycles_and_packs,
            'author'           => $author_name,
            'title'            => $decklist_title,
            'list_mwl'         => $list_mwl,
            'mwl_code'         => $mwl_code,
            'list_rotations'   => $list_rotations,
            'rotation_id'      => $rotation_id,
            'is_legal'         => $is_legal,
        ];
        $params['sort_' . $sort] = ' selected="selected"';
        if (!empty($faction_code)) {
            if ($faction_code == 'corp') {
                $params['faction_C'] = ' selected="selected"';
            } elseif ($faction_code == 'runner') {
                $params['faction_R'] = ' selected="selected"';
            } else {
                $params['faction_' . $faction_code] = ' selected="selected"';
            }
        }

        if (!empty($cards_code) && is_array($cards_code)) {
            $cards = $dbh->executeQuery(
                "SELECT
                    c.title,
                    c.code,
                    f.code faction_code,
                    p.name pack_name
                    FROM card c
                    JOIN faction f ON f.id=c.faction_id
                    JOIN pack p ON p.id=c.pack_id
                    WHERE c.code IN (?)
                    ORDER BY c.code DESC",
                [$cards_code],
                [Connection::PARAM_INT_ARRAY]
            )->fetchAll();

            $params['cards'] = '';
            foreach ($cards as $card) {
                $params['cards'] .= $this->renderView('/Search/card.html.twig', $card);
            }
        }

        return $this->renderView('/Search/form.html.twig', $params);
    }

    /**
     * @param string                    $decklist1_uuid
     * @param string                    $decklist2_uuid
     * @param EntityManagerInterface $entityManager
     * @param DiffService            $diffService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function diffAction(string $decklist1_uuid, string $decklist2_uuid, EntityManagerInterface $entityManager, DiffService $diffService)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        /** @var Decklist $d1 */
        $d1 = $entityManager->getRepository('AppBundle:Decklist')->findOneBy(["uuid" => $decklist1_uuid]);
        /** @var Decklist $d2 */
        $d2 = $entityManager->getRepository('AppBundle:Decklist')->findOneBy(["uuid" => $decklist2_uuid]);

        if (!$d1 || !$d2) {
            throw $this->createNotFoundException();
        }

        $decks = [$d1->getContent(), $d2->getContent()];

        list($listings, $intersect) = $diffService->diffContents($decks);

        $content1 = [];
        foreach ($listings[0] as $code => $qty) {
            $card = $entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $code]);
            if ($card instanceof Card) {
                $content1[] = [
                    'title' => $card->getTitle(),
                    'code'  => $code,
                    'qty'   => $qty,
                ];
            }
        }

        $content2 = [];
        foreach ($listings[1] as $code => $qty) {
            $card = $entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $code]);
            if ($card instanceof Card) {
                $content2[] = [
                    'title' => $card->getTitle(),
                    'code'  => $code,
                    'qty'   => $qty,
                ];
            }
        }

        $shared = [];
        foreach ($intersect as $code => $qty) {
            $card = $entityManager->getRepository('AppBundle:Card')->findOneBy(['code' => $code]);
            if ($card instanceof Card) {
                $shared[] = [
                    'title' => $card->getTitle(),
                    'code'  => $code,
                    'qty'   => $qty,
                ];
            }
        }

        return $this->render(
            '/Diff/decklistsDiff.html.twig',
            [
                'decklist1' => [
                    'faction_code' => $d1->getFaction()->getCode(),
                    'name'         => $d1->getName(),
                    'id'           => $d1->getId(),
                    'uuid'         => $d1->getUuid(),
                    'prettyname'   => $d1->getPrettyname(),
                    'content'      => $content1,
                ],
                'decklist2' => [
                    'faction_code' => $d2->getFaction()->getCode(),
                    'name'         => $d2->getName(),
                    'id'           => $d2->getId(),
                    'uuid'         => $d2->getUuid(),
                    'prettyname'   => $d2->getPrettyname(),
                    'content'      => $content2,
                ],
                'shared'    => $shared,
            ]
        );
    }
}

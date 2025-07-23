<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Service\CardsData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FactionController extends Controller
{
    /**
     * @param string                 $faction_code
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function factionAction(string $faction_code, EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        if ($faction_code === 'mini-factions') {
            $factions = $entityManager->getRepository('AppBundle:Faction')->findBy(['isMini' => true], ['code' => 'ASC']);
            $faction_name = "Mini-factions";
        } else {
            $factions = $entityManager->getRepository('AppBundle:Faction')->findBy(['code' => $faction_code]);
            if (!count($factions)) {
                throw $this->createNotFoundException();
            }
            $faction_name = $factions[0]->getName();
        }

        $result = [];
        $banned_cards = array();

        foreach ($factions as $faction) {
            $identities = $entityManager->getRepository(Card::class)->findByFaction($faction);
            $identitiesGroupsByName = CardsData::groupCardsByTitle($identities);

            // build the list of the top $decklists_per_id decklists per id
            // also, compute the total points of those decks per id
            $decklists_per_id = 3;
            $identitiesWithDeckLists = [];
            foreach (array_values($identitiesGroupsByName) as $identitiesGroup) {
                $qb = $entityManager->createQueryBuilder();
                $qb->select('d, (d.nbvotes/(1+DATE_DIFF(CURRENT_TIMESTAMP(),d.dateCreation)/10)) as points')
                    ->from('AppBundle:Decklist', 'd')
                    ->where('d.identity in (:identities)')
                    ->setParameter('identities', $identitiesGroup)
                    ->orderBy('points', 'DESC')
                    ->setMaxResults($decklists_per_id);
                $results = $qb->getQuery()->getResult();

                $bannedIdentities = $cardsData->getBannedCardCodesInArray($identitiesGroup);
                $isIdentityBanned = count($bannedIdentities) > 0;

                $latestVersionOfIdentity = CardsData::getLatestVersionForEachCards($identitiesGroup)[0];

                $list = array_map(function ($row) {
                    return $row[0];
                }, $results);

                $identitiesWithDeckLists[] = [
                    'identity'  => $latestVersionOfIdentity,
                    'isBanned' => $isIdentityBanned,
                    'isRotated' => $latestVersionOfIdentity->getPack()->getCycle()->getRotated(),
                    'decklists' => $list,
                ];
            }
            
            // Sort the list by identity alphabetically.
            usort($identitiesWithDeckLists, function ($a, $b) {
                $statusA = $this->getStatus($a);
                $statusB = $this->getStatus($b) ?? 999;

                if ($statusA < $statusB) return -1;
                if ($statusA > $statusB) return 1;

                return strcasecmp($a['identity']->getTitle(), $b['identity']->getTitle());
            });

            $result[] = [
                'faction'   => $faction,
                'decklists' => $identitiesWithDeckLists,
            ];
        }

        return $this->render('/Faction/faction.html.twig', [
            "pagetitle"       => "Faction Page: $faction_name",
            "pagedescription" => "Explore all $faction_name identities and recent decklists.",
            "results"         => $result,
        ], $response);
    }

    private function getStatus($card) {
        $statusOrder = [
            'normal'  => 0,
            'banned'  => 1,
            'rotated' => 2,
        ];
        if ($card['isBanned']) {
            return $statusOrder['banned'];
        } elseif ($card['isRotated']) {
            return $statusOrder['rotated'];
        }
        return $statusOrder['normal'];
    }
}

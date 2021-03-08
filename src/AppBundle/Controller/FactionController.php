<?php

namespace AppBundle\Controller;

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

            // build the list of identites for the faction

            $qb = $entityManager->createQueryBuilder();
            $qb->select('c')
               ->from('AppBundle:Card', 'c')
               ->join('c.pack', 'p')
               ->where('c.faction=:faction')
               ->setParameter('faction', $faction)
               ->andWhere('c.type=:type')
               ->andWhere('p.dateRelease is not null')
               ->setParameter('type', $entityManager->getRepository('AppBundle:Type')->findOneBy(['code' => 'identity']));

            $identities = $qb->getQuery()->getResult();

            $uniqueIdentities = [];
            foreach ($identities as $card) {
                $title = $card->getTitle();
                if (!isset($uniqueIdentities[$title])) {
                    $uniqueIdentities[$title] = [];
                }
                $uniqueIdentities[$title][] = $card;
            }

            $nb_decklists_per_id = 3;

            // build the list of the top $nb_decklists_per_id decklists per id
            // also, compute the total points of those decks per id

            $decklists = [];
            foreach (array_values($uniqueIdentities) as $identities) {
                $qb = $entityManager->createQueryBuilder();
                $qb->select('d, (d.nbvotes/(1+DATE_DIFF(CURRENT_TIMESTAMP(),d.dateCreation)/10)) as points')
                   ->from('AppBundle:Decklist', 'd')
                   ->where('d.identity in (:identities)')
                   ->setParameter('identities', $identities)
                   ->orderBy('points', 'DESC')
                   ->setMaxResults($nb_decklists_per_id);
                $results = $qb->getQuery()->getResult();

                $points = 0;
                $list = [];
                foreach ($results as $row) {
                    $list[] = $row[0];
                    $points += intval($row['points']);
                }

                $identity = $cardsData->select_only_latest_cards($identities);

                $i = $cardsData->get_mwl_info([$identity[0]]);
                if (count($i) > 0 && $i[array_keys($i)[0]]['active'] && $i[array_keys($i)[0]]['deck_limit'] == 0) {
                    $banned_cards[$identity[0]->getCode()] = true;
                }

                $decklists[] = [
                    'identity'  => $identity[0],
                    'points'    => $points,
                    'decklists' => $list,
                ];
            }

            // Sort the identities alphabetically. 
            usort($decklists, function ($a, $b) {
                return strcasecmp($a['identity']->getTitle(), $b['identity']->getTitle());
            });

            $result[] = [
                'faction'   => $faction,
                'decklists' => $decklists,
            ];
        }

        return $this->render('/Faction/faction.html.twig', [
            "pagetitle"    => "Faction Page: $faction_name",
            "results"      => $result,
            "banned_cards" => $banned_cards,
        ], $response);
    }
}

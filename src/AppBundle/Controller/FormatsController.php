<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mwl;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FormatsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(EntityManagerInterface $entityManager)
    {
        $q = $entityManager->createQuery("SELECT c FROM AppBundle:Cycle c where c.code IN ('system-gateway', 'system-update-2021', 'ashes', 'borealis') ORDER BY c.position DESC");
        $startup_cycles = $q->getResult();
        $startup_packs = array();
        foreach ($startup_cycles as $cycle) {
            foreach ($cycle->getPacks() as $pack) {
                $startup_packs[] = $pack->getCode();
            }
        }

        $q = $entityManager->createQuery("SELECT c FROM AppBundle:Cycle c WHERE c.rotated = 0 AND c.code NOT IN ('draft', 'napd', 'roseville') ORDER BY c.position DESC");
        $standard_cycles = $q->getResult();

        $standard_packs = array();
        foreach ($standard_cycles as $cycle) {
            foreach ($cycle->getPacks() as $pack) {
                $standard_packs[] = $pack->getCode();
            }
        }

        $standard_banlist = $entityManager->getRepository('AppBundle:Mwl')->findOneBy(["active" => 1]);

        $dbh = $entityManager->getConnection();
        $num_standard_cards = $dbh->executeQuery(
            "SELECT COUNT(DISTINCT card.title) as num_cards"
            . " FROM card"
            . " JOIN pack ON card.pack_id = pack.id"
            . " JOIN cycle ON pack.cycle_id = cycle.id"
            . " WHERE cycle.rotated = 0 AND cycle.code NOT IN ('draft', 'napd', 'roseville')"
        )->fetch(\PDO::FETCH_ASSOC)['num_cards'];
        $num_startup_cards = $dbh->executeQuery(
            "SELECT COUNT(DISTINCT card.title) as num_cards"
            . " FROM card"
            . " JOIN pack ON card.pack_id = pack.id"
            . " JOIN cycle ON pack.cycle_id = cycle.id"
            . " WHERE cycle.code IN ('system-gateway', 'system-update-2021', 'ashes', 'borealis')"
        )->fetch(\PDO::FETCH_ASSOC)['num_cards'];



        return $this->render('/Formats/formats.html.twig', [
            'pagetitle'          => "Play Formats",
            'startup_cycles'     => $startup_cycles,
            'startup_packs'      => $startup_packs,
            'num_startup_cards'  => $num_startup_cards,
            'standard_cycles'    => $standard_cycles,
            'standard_packs'     => $standard_packs,
            'standard_banlist'   => $standard_banlist,
            'num_standard_cards' => $num_standard_cards,
        ]);
    }
}

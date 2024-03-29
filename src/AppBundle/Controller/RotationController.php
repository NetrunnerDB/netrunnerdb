<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mwl;
use AppBundle\Service\CardsData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RotationController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(EntityManagerInterface $entityManager, CardsData $cardsData)
    {
        $r = $entityManager->createQuery("SELECT r FROM AppBundle:Rotation r ORDER BY r.dateStart DESC");

        return $this->render('/Rotation/rotation.html.twig', [
            'pagetitle'        => "Rotation",
            'pagedescription'  => "Compare the different card pools from the Standard format.",
            'rotations'        => $r->getResult(),
            'cycles_and_packs' => $cardsData->getCyclesAndPacks(),
       ]);
    }
}

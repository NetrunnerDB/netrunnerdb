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
        return $this->render('/Formats/formats.html.twig', [
            'pagetitle'       => "Play Formats",
            'pagedescription' => "See the official formats for Netrunner play.",
        ]);
    }
}

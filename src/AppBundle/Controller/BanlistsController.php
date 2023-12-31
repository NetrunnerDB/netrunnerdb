<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BanlistsController extends Controller
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('/Banlists/banlists.html.twig', [
            'format'                        => $request->query->get('format'),
            'restriction'                   => $request->query->get('restriction'),
            'pagetitle'                     => "Ban Lists",
            'pagedescription'               => "View the ban lists for each format from throughout the game's history.",
        ]);
    }
}

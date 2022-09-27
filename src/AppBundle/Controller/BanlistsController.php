<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BanlistsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(EntityManagerInterface $entityManager)
    {
        return $this->render('/Banlists/banlists.html.twig', [
            'pagetitle'                     => "Ban Lists",
        ]);
    }
}

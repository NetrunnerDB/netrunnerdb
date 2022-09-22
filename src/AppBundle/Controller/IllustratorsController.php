<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IllustratorsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(EntityManagerInterface $entityManager)
    {
        return $this->render('/Illustrators/illustrators.html.twig', [
            'pagetitle'                     => "Illustrators",
        ]);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Legality;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BanlistsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(EntityManagerInterface $entityManager)
    {
        $mwls = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);
		$banlists = array();
        foreach ($mwls as $mwl) {
			$x = array();
			$x['name'] = $mwl->getName();
			$x['active'] = $mwl->getActive();
			$x['code'] = $mwl->getCode();
			$x['start_date'] = $mwl->getDateStart();
			$x['cards'] = $mwl->getCards();
			$x['mwl_object_delete'] = $mwl;
			$banlists[] = $x;
        }

        return $this->render('/Banlists/banlists.html.twig', [
         'pagetitle'  => "Ban Lists",
         'banlists'   => $banlists,
        ]);
    }
}

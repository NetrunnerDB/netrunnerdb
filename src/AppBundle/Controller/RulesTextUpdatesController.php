<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RulesTextUpdatesController extends Controller
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('/RulesTextUpdates/rules_text_updates.html.twig', [
            'format'                        => $request->query->get('format'),
            'restriction'                   => $request->query->get('restriction'),
            'pagetitle'                     => "Rules Text Updates",
            'pagedescription'               => "View the rules text updates by the NSG Rules Team.",
        ]);
    }
}

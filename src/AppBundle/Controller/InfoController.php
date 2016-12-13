<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends Controller
{
    public function getAction (Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return new JsonResponse(['id' => null], Response::HTTP_OK);
        }

        /* @var $helper \AppBundle\Helper\PersonalizationHelper */
        $helper = $this->get('personalization_helper');

        $content = $helper->defaultBlock($user);

        if ($request->query->has('decklist_id')) {
            $decklist = $em->getRepository('AppBundle:Decklist')->find($request->query->get('decklist_id'));
            if ($decklist) {
                $content = array_merge($content, $helper->decklistBlock($user, $decklist));
            }
        }

        if ($request->query->has('card_id')) {
            $card = $em->getRepository('AppBundle:Card')->find($request->query->get('card_id'));
            if ($card) {
                $content = array_merge($content, $helper->cardBlock($user, $card));
            }
        }

        $response = new JsonResponse($content);
        $response->setPrivate();

        return $response;
    }
}

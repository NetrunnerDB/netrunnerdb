<?php

namespace AppBundle\Controller;

use AppBundle\Service\PersonalizationHelper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends Controller
{
    public function getAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return new JsonResponse(['is_authenticated' => false]);
        }

        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();

        $helper = $this->get(PersonalizationHelper::class);

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

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

        return new JsonResponse($content);
    }
}

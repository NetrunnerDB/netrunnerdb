<?php

namespace AppBundle\Controller;

use AppBundle\Service\PersonalizationHelper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends Controller
{
    public function getAction(Request $request, EntityManagerInterface $entityManager)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return new JsonResponse(['is_authenticated' => false]);
        }

        /** @var User $user */
        $user = $this->getUser();

        $helper = $this->get(PersonalizationHelper::class);

        $content = $helper->defaultBlock($user);

        if ($request->query->has('decklist_id')) {
            $decklist = $entityManager->getRepository('AppBundle:Decklist')->find($request->query->get('decklist_id'));
            if ($decklist) {
                $content = array_merge($content, $helper->decklistBlock($user, $decklist));
            }
        }

        if ($request->query->has('card_id')) {
            $card = $entityManager->getRepository('AppBundle:Card')->find($request->query->get('card_id'));
            if ($card) {
                $content = array_merge($content, $helper->cardBlock($user, $card));
            }
        }

        return new JsonResponse($content);
    }
}

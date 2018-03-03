<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Decklist;
use AppBundle\Service\PersonalizationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class InfoController extends Controller
{
    /**
     * @param Request                       $request
     * @param EntityManagerInterface        $entityManager
     * @param PersonalizationHelper         $helper
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return JsonResponse
     */
    public function getAction(
        Request $request,
        EntityManagerInterface $entityManager,
        PersonalizationHelper $helper,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        if (!$authorizationChecker->isGranted('ROLE_USER')) {
            return new JsonResponse(['is_authenticated' => false]);
        }

        $user = $this->getUser();

        $content = $helper->defaultBlock($user);

        if ($request->query->has('decklist_id')) {
            $decklist = $entityManager->getRepository('AppBundle:Decklist')->find($request->query->get('decklist_id'));
            if ($decklist instanceof Decklist) {
                $content = array_merge($content, $helper->decklistBlock($user, $decklist));
            }
        }

        if ($request->query->has('card_id')) {
            $card = $entityManager->getRepository('AppBundle:Card')->find($request->query->get('card_id'));
            if ($card instanceof Card) {
                $content = array_merge($content, $helper->cardBlock($user, $card));
            }
        }

        return new JsonResponse($content);
    }
}

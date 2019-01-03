<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Faction;
use AppBundle\Service\CardsData;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function profileAction(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $factions = $entityManager->getRepository('AppBundle:Faction')->findAll();
        /** @var Faction $faction */
        foreach ($factions as $i => $faction) {
            $factions[$i]->localizedName = $faction->getName();
        }

        return $this->render('/Default/private_profile.html.twig', [
            'user' => $user, 'factions' => $factions]);
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function saveProfileAction(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $username = filter_var($request->get('username'), FILTER_SANITIZE_STRING);
        if ($username !== $user->getUsername()) {
            $user_existing = $entityManager->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

            if ($user_existing) {
                $this->addFlash('error', "Username $username is already taken.");

                return $this->redirect($this->generateUrl('user_profile'));
            }

            $user->setUsername($username);
        }

        $email = filter_var($request->get('email'), FILTER_SANITIZE_STRING);
        if ($email !== $user->getEmail()) {
            $user->setEmail($email);
        }

        $resume = filter_var($request->get('resume'), FILTER_SANITIZE_STRING);
        $faction_code = filter_var($request->get('user_faction_code'), FILTER_SANITIZE_STRING);
        $notifAuthor = $request->get('notif_author') ? true : false;
        $notifCommenter = $request->get('notif_commenter') ? true : false;
        $notifMention = $request->get('notif_mention') ? true : false;
        $shareDecks = $request->get('share_decks') ? true : false;
        $autoloadImages = $request->get('autoload_images') ? true : false;

        $user->setFaction($faction_code);
        $user->setResume($resume);
        $user->setNotifAuthor($notifAuthor);
        $user->setNotifCommenter($notifCommenter);
        $user->setNotifMention($notifMention);
        $user->setShareDecks($shareDecks);
        $user->setAutoloadImages($autoloadImages);

        $entityManager->flush();

        $this->addFlash('notice', "Successfully saved your profile.");

        return $this->redirect($this->generateUrl('user_profile'));
    }

    /**
     * @param string                 $introduction
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function validateIntroductionAction(string $introduction, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $introductions = $user->getIntroductions();
        if (!$introductions) {
            $introductions = [];
        }
        $introductions[$introduction] = true;
        $user->setIntroductions($introductions);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function resetIntroductionsAction(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $user->setIntroductions(null);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * @return Response
     */
    public function aboutAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        return $this->render('/Default/about.html.twig', [
            "pagetitle" => "About NetrunnerDB",
        ], $response);
    }

    /**
     * @return Response
     */
    public function syntaxAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        return $this->render('/Default/syntax.html.twig', [
            "pagetitle" => "Search Syntax Reference",
        ], $response);
    }
}

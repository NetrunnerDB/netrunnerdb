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
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function saveProfileAction(Request $request, EntityManagerInterface $entityManager, Session $session)
    {
        $user = $this->getUser();

        $username = filter_var($request->get('username'), FILTER_SANITIZE_STRING);
        if ($username !== $user->getUsername()) {
            $user_existing = $entityManager->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

            if ($user_existing) {
                $session
                     ->getFlashBag()
                     ->set('error', "Username $username is already taken.");

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

        $session
             ->getFlashBag()
             ->set('notice', "Successfully saved your profile.");

        return $this->redirect($this->generateUrl('user_profile'));
    }

    /**
     * tags an introduction as completed
     *
     * @param string $introduction
     */
    public function validateIntroductionAction($introduction, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if ($user) {
            $introductions = $user->getIntroductions();
            if (!$introductions) {
                $introductions = [];
            }
            $introductions[$introduction] = true;
            $user->setIntroductions($introductions);
            $entityManager->flush();
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * resets all introductions to "uncompleted"
     */
    public function resetIntroductionsAction(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if ($user) {
            $user->setIntroductions(null);
            $entityManager->flush();
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }

    public function rulesAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        $page = $this->get(CardsData::class)->replaceSymbols($this->renderView('/Default/rules.html.twig', ["pagetitle" => "Rules", "pagedescription" => "Refer to the official rules of the game."]));

        $response->setContent($page);

        return $response;
    }

    public function aboutAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('long_cache'));

        return $this->render('/Default/about.html.twig', [
            "pagetitle" => "About",
        ], $response);
    }
}

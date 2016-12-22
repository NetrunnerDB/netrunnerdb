<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{

    public function profileAction ()
    {
        $user = $this->getUser();

        $factions = $this->get('doctrine')->getRepository('AppBundle:Faction')->findAll();
        foreach($factions as $i => $faction) {
            $factions[$i]->localizedName = $faction->getName();
        }

        return $this->render('AppBundle:Default:private_profile.html.twig', array(
                    'user' => $user, 'factions' => $factions));
    }

    public function saveProfileAction ()
    {
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
        $request = $request;
        $em = $this->getDoctrine()->getManager();

        $username = filter_var($request->get('username'), FILTER_SANITIZE_STRING);
        if($username !== $user->getUsername()) {
            $user_existing = $em->getRepository('AppBundle:User')->findOneBy(array('username' => $username));

            if($user_existing) {

                $this->get('session')
                        ->getFlashBag()
                        ->set('error', "Username $username is already taken.");

                return $this->redirect($this->generateUrl('user_profile'));
            }

            $user->setUsername($username);
        }

        $email = filter_var($request->get('email'), FILTER_SANITIZE_STRING);
        if($email !== $user->getEmail()) {
            $user->setEmail($email);
        }

        $resume = filter_var($request->get('resume'), FILTER_SANITIZE_STRING);
        $faction_code = filter_var($request->get('user_faction_code'), FILTER_SANITIZE_STRING);
        $notifAuthor = $request->get('notif_author') ? TRUE : FALSE;
        $notifCommenter = $request->get('notif_commenter') ? TRUE : FALSE;
        $notifMention = $request->get('notif_mention') ? TRUE : FALSE;
        $shareDecks = $request->get('share_decks') ? TRUE : FALSE;
        $autoloadImages = $request->get('autoload_images') ? TRUE : FALSE;

        $user->setFaction($faction_code);
        $user->setResume($resume);
        $user->setNotifAuthor($notifAuthor);
        $user->setNotifCommenter($notifCommenter);
        $user->setNotifMention($notifMention);
        $user->setShareDecks($shareDecks);
        $user->setAutoloadImages($autoloadImages);

        $this->get('doctrine')->getManager()->flush();

        $this->get('session')
                ->getFlashBag()
                ->set('notice', "Successfully saved your profile.");

        return $this->redirect($this->generateUrl('user_profile'));
    }

    /**
     * tags an introduction as completed
     * @param string $introduction
     * @param Request $request
     */
    public function validateIntroductionAction ($introduction)
    {
        $user = $this->getUser();
        if($user) {
            $introductions = $user->getIntroductions();
            if(!$introductions)
                $introductions = [];
            $introductions[$introduction] = true;
            $user->setIntroductions($introductions);
            $this->getDoctrine()->getManager()->flush();
        }
        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * resets all introductions to "uncompleted"
     * @param Request $request
     */
    public function resetIntroductionsAction ()
    {
        $user = $this->getUser();
        if($user) {
            $user->setIntroductions(null);
            $this->getDoctrine()->getManager()->flush();
        }
        return new JsonResponse([
            'success' => true
        ]);
    }

    function rulesAction ()
    {

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));

        $page = $this->get('cards_data')->replaceSymbols($this->renderView('AppBundle:Default:rules.html.twig', array("pagetitle" => "Rules", "pagedescription" => "Refer to the official rules of the game.")));

        $response->setContent($page);
        return $response;
    }

    function aboutAction ()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));

        return $this->render('AppBundle:Default:about.html.twig', array(
                    "pagetitle" => "About",
                        ), $response);
    }

    function apidocAction ()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));


        return $this->render('AppBundle:Default:apidoc.html.twig', array(
                    "pagetitle" => "API documentation",
                        ), $response);
    }

}

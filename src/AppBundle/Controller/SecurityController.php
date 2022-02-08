<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        return $this->render(
            '/Security/login.html.twig',
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error'         => ($authenticationUtils->getLastAuthenticationError() == null ? "" : "Invalid credentials."),
            ]
        );
    }

    public function registerAction()
    {
        $session = $this->get('session');

        $session->getFlashBag()->add(
            'warning',
            'Registration is currently disabled.'
        );

        return $this->redirectToRoute('netrunnerdb_index');
    }
}

<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class RedirectAfterLogout implements LogoutSuccessHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router) {
      $this->router = $router;
    }

    /**
     * Send the user to a specified URL if redirect_to is specified in the URL instead of the homepage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        return new RedirectResponse($request->query->get('redirect_to') ?
            $request->query->get('redirect_to') : $this->router->generate('netrunnerdb_index')
        );
    }
} 

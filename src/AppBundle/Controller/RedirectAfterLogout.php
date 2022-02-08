<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class RedirectAfterLogout implements LogoutSuccessHandlerInterface
{
    /**
     * Send the user to a specified URL if redirect_to is specified in the URL instead of the homepage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->query->get('redirect_to')) {
            $response = new RedirectResponse($request->query->get('redirect_to'));
        } else {
            $response = new RedirectResponse($this->generateUrl('netrunnerdb_index'));
        }
        return $response;
    }
} 

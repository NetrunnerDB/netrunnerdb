<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Connects a user via oauth2
 *
 * @author cbertolini
 * @Route("/oauth")
 */
class OauthController extends Controller
{
    /**
     * Display the API explorer
     * @Route("/explorer")
     * @Method("GET")
     * @Template("/Oauth/explorer.html.twig")
     */
    public function explorerAction(Request $request)
    {
        $session = $request->getSession();

        if ($session instanceof SessionInterface && $session->has('oauth_token_response')) {
            return [
                'token' => $session->get('oauth_token_response'),
            ];
        }

        return $this->redirectToRoute('app_oauth_initiate');
    }

    /**
     * Display a page with "Connect to NetrunnerDB" button
     *
     * @Route("/initiate")
     * @Method("GET")
     * @Template("/Oauth/initiate.html.twig")
     */
    public function initiateAction()
    {
        return [
            'client_id'    => $this->getParameter('oauth_test_client_id'),
            'redirect_uri' => $this->getParameter('oauth_test_redirect_uri'),
        ];
    }

    /**
     * Receive the authorization code and request an access token
     * @param Request $request
     * @Route("/callback")
     * @Method("GET")
     */
    public function callbackAction(Request $request)
    {
        // receive the aothorization code
        $code = $request->get('code');

        // request the access-token to the oauth server
        $url = $this->generateUrl(
            'fos_oauth_server_token',
            [
                'client_id'     => $this->getParameter('oauth_test_client_id'),
                'client_secret' => $this->getParameter('oauth_test_client_secret'),
                'redirect_uri'  => $this->getParameter('oauth_test_redirect_uri'),
                'grant_type'    => 'authorization_code',
                'code'          => $code,
            ],
            UrlGenerator::ABSOLUTE_URL
        );

        $client = new Client();
        $res = $client->request('GET', $url);
        if ($res->getStatusCode() !== 200) {
            throw new \Exception($res->getReasonPhrase());
        }

        // process the response
        $response = json_decode($res->getBody(), true);
        $now = new \DateTime();
        $response['creation_date'] = $now->format('c');
        $now->add(\DateInterval::createFromDateString($response['expires_in'] . ' seconds'));
        $response['expiration_date'] = $now->format('c');

        // store the response
        $session = $request->getSession();
        if (!$session instanceof SessionInterface) {
            $session = new Session();
            $session->start();
        }
        $session->set('oauth_token_response', $response);

        // redirect to the explorer
        return $this->redirectToRoute('app_oauth_explorer');
    }
}

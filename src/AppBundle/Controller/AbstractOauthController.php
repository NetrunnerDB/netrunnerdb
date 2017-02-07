<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer as Serializer2;

/**
 * Description of AbstractOauthController
 *
 * @author cbertolini
 */
abstract class AbstractOauthController extends Controller
{

    /**
     * 
     * @param Request $request
     * @return Client
     */
    public function getOauthClient ()
    {
        $tokenManager = $this->container->get('fos_oauth_server.access_token_manager.default');
        $token = $this->container->get('security.token_storage')->getToken();
        $accessToken = $tokenManager->findTokenByToken($token->getToken());

        return $accessToken->getClient();
    }

    /**
     * 
     * @param integer $status
     * @param mixed $data
     * @param string $message
     * @return array
     */
    public function getJsendResponse ($status, $data, $message = null)
    {
        $response = [
            "status" => $status,
            "data" => $data
        ];

        if (isset($message)) {
            $response['message'] = $message;
        }

        return $response;
    }

    /**
     * 
     * @param array $data
     * @param integer $status
     * @param array $headers
     * @return Response
     */
    public function createJsonResponse ($data, $status = 200, $headers = [])
    {
        /* @var $serializer Serializer */
        $serializer = $this->get('jms_serializer');

        $context = new \JMS\Serializer\SerializationContext();
        $context->setSerializeNull(true);

        $content = $serializer->serialize($data, 'json', $context);
        $response = new Response($content, $status, $headers);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}

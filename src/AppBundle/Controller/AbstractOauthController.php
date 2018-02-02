<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of AbstractOauthController
 *
 * @author cbertolini
 */
abstract class AbstractOauthController extends Controller
{
    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var ArrayTransformerInterface $arrayTransformer */
    protected $arrayTransformer;

    public function __construct(SerializerInterface $serializer, ArrayTransformerInterface $arrayTransformer)
    {
        $this->serializer = $serializer;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @return Client
     */
    public function getOauthClient()
    {
        $tokenManager = $this->container->get('fos_oauth_server.access_token_manager.default');
        $token = $this->container->get('security.token_storage')->getToken();
        $accessToken = $tokenManager->findTokenBy(['user' => $token->getUser()]);

        return $accessToken->getClient();
    }

    /**
     * @param string $status
     * @param array $data
     * @param string|null $message
     * @return array
     */
    public function getJsendResponse(string $status, array $data = [], string $message = null)
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
    public function createJsonResponse($data, $status = 200, $headers = [])
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $content = $this->serializer->serialize($data, 'json', $context);
        $response = new Response($content, $status, $headers);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use FOS\OAuthServerBundle\Entity\AccessTokenManager;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
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

    /** @var AccessTokenManager $accessTokenManager */
    private $accessTokenManager;

    public function __construct(
        SerializerInterface $serializer,
        ArrayTransformerInterface $arrayTransformer,
        AccessTokenManager $accessTokenManager
    ) {
        $this->serializer = $serializer;
        $this->arrayTransformer = $arrayTransformer;
        $this->accessTokenManager = $accessTokenManager;
    }

    /**
     * @return Client
     */
    public function getOauthClient()
    {
        return $this
            ->accessTokenManager
            ->findTokenBy([
                'user' => $this->getUser(),
            ])
            ->getClient();
    }

    /**
     * @param string      $status
     * @param array       $data
     * @param string|null $message
     * @return array
     */
    public function getJsendResponse(string $status, array $data = [], string $message = null)
    {
        $response = [
            "status" => $status,
            "data"   => $data,
        ];

        if (isset($message)) {
            $response['message'] = $message;
        }

        return $response;
    }

    /**
     *
     * @param array   $data
     * @param integer $status
     * @param array   $headers
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

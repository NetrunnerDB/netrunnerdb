<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use FOS\OAuthServerBundle\Entity\AccessTokenManager;
use FOS\OAuthServerBundle\Model\ClientManager;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var AccessTokenManager $accessTokenManager */
    private $accessTokenManager;

    public function __construct(
        SerializerInterface $serializer,
        ArrayTransformerInterface $arrayTransformer,
        TokenStorageInterface $tokenStorage,
        AccessTokenManager $accessTokenManager
    ) {
        $this->serializer = $serializer;
        $this->arrayTransformer = $arrayTransformer;
        $this->tokenStorage = $tokenStorage;
        $this->accessTokenManager = $accessTokenManager;
    }

    /**
     * @return Client
     */
    public function getOauthClient()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw $this->createAccessDeniedException();
        }

        return $this
            ->accessTokenManager
            ->findTokenBy([
                'user' => $token->getUser(),
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

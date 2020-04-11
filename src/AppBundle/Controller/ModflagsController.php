<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ModflagsController
 *
 * @author cbertolini
 */
class ModflagsController extends Controller
{
    /**
     * @return Response
     *
     * @IsGranted("ROLE_MODERATOR")
     */
    public function getAction(EntityManagerInterface $entityManager)
    {
        $modflags = $entityManager->getRepository('AppBundle:Modflag')->findAll();

        $content = [
            'count' => count($modflags),
            'data' => $modflags
        ];

        $serializer = SerializerBuilder::create()->build();

        $response = new Response();
        $response->setContent($serializer->serialize($content, 'json'));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}

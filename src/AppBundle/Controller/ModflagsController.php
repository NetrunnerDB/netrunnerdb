<?php

namespace AppBundle\Controller;

use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ModflagsController
 *
 * @author cbertolini
 */
class ModflagsController extends Controller
{
    public function getAction()
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException('Access denied');
        }
        
        $modflags = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:Modflag')->findAll();
        
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

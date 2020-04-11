<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends Controller
{
    /**
     * @return Response
     */
    public function docAction()
    {
        return $this->render('/Default/apiIntro.html.twig');
    }
}

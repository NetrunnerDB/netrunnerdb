<?php

namespace Netrunnerdb\BuilderBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ToolsController extends Controller
{
    public function demoAction()
    {
        return $this->render('NetrunnerdbBuilderBundle:Tools:demo.html.twig');
    }
    
    
    public function extdecklistAction($id)
    {
    	return $this->render('NetrunnerdbBuilderBundle:Tools:demo-ext-decklist.html.twig', [
    			'id' => $id
    	]);
    }
}
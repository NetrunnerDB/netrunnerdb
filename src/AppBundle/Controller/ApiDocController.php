<?php 

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiDocController extends Controller
{
	public function docAction()
	{
		return $this->render('AppBundle:Default:apiIntro.html.twig');
	}
}
<?php 

namespace Netrunnerdb\BuilderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiDocController extends Controller
{
	public function docAction()
	{
		return $this->render('NetrunnerdbBuilderBundle:Default:apiIntro.html.twig');
	}
}
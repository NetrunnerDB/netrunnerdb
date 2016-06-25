<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netrunnerdb\CardsBundle\Entity\Card;
use Netrunnerdb\CardsBundle\Entity\Pack;
use Netrunnerdb\CardsBundle\Entity\Cycle;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
	public function searchAction(Request $request)
	{
	    $response = new Response();
	    $response->setPublic();
	    $response->setMaxAge($this->container->getParameter('long_cache'));
	     
		$dbh = $this->get('doctrine')->getConnection();
	
		$list_packs = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findBy(array(), array("dateRelease" => "ASC", "position" => "ASC"));
		$packs = array();
		foreach($list_packs as $pack) {
			$packs[] = array(
					"name" => $pack->getName(),
					"code" => $pack->getCode(),
			);
		}
	
		$list_cycles = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findBy(array(), array("position" => "ASC"));
		$cycles = array();
		foreach($list_cycles as $cycle) {
			$cycles[] = array(
					"name" => $cycle->getName(),
					"code" => $cycle->getCode(),
			);
		}
	
		$list_types = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Type')->findBy(array(), array("name" => "ASC"));
		$types = array_map(function ($type) {
			return $type->getName();
		}, $list_types);
	
		$list_keywords = $dbh->executeQuery("SELECT DISTINCT c.keywords FROM card c WHERE c.keywords != ''")->fetchAll();
		$keywords = array();
		foreach($list_keywords as $keyword) {
			$subs = explode(' - ', $keyword["keywords"]);
			foreach($subs as $sub) {
				$keywords[$sub] = 1;
			}
		}
		$keywords = array_keys($keywords);
		sort($keywords);
	
		$list_illustrators = $dbh->executeQuery("SELECT DISTINCT c.illustrator FROM card c WHERE c.illustrator != '' ORDER BY c.illustrator")->fetchAll();
		$illustrators = array_map(function ($elt) {
			return $elt["illustrator"];
		}, $list_illustrators);
	
		return $this->render('NetrunnerdbCardsBundle:Search:searchform.html.twig', array(
		        "pagetitle" => "Card Search",
		        "pagedescription" => "Find all the cards of the game, easily searchable.",
				"packs" => $packs,
				"cycles" => $cycles,
				"types" => $types,
				"keywords" => $keywords,
				"illustrators" => $illustrators,
				"allsets" => $this->renderView('NetrunnerdbCardsBundle:Default:allsets.html.twig', array(
                    "data" => $this->get('cards_data')->allsetsdata(),
		        )),
		), $response);
	}
	
	function rulesAction(Request $request)
	{
        
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge($this->container->getParameter('long_cache'));
		
		$page = $this->get('cards_data')->replaceSymbols($this->renderView('NetrunnerdbCardsBundle:Default:rules.html.twig',
		        array("pagetitle" => "Rules", "pagedescription" => "Refer to the official rules of the game.")));
		
		$response->setContent($page);
		return $response;
	}
	
	function aboutAction(Request $request)
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge($this->container->getParameter('long_cache'));
		
		return $this->render('NetrunnerdbCardsBundle:Default:about.html.twig', array(
		        "pagetitle" => "About",
		), $response);
	}

	function apidocAction(Request $request)
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge($this->container->getParameter('long_cache'));
		
		
		return $this->render('NetrunnerdbCardsBundle:Default:apidoc.html.twig', array(
		        "pagetitle" => "API documentation",
		), $response);
	}
}

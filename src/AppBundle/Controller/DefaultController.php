<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Card;
use AppBundle\Entity\Pack;
use AppBundle\Entity\Cycle;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function profileAction(Request $request)
    {
    	$user = $this->getUser();
    	
    	$factions = $this->get('doctrine')->getRepository('AppBundle:Faction')->findAll();
    	foreach($factions as $i => $faction) {
    		$factions[$i]->localizedName = $faction->getName();
    	}
    	
    	return $this->render('AppBundle:Default:private_profile.html.twig', array(
    			'user'=> $user, 'factions' => $factions));
    }
    
    public function saveProfileAction(Request $request)
    {
    	/* @var $user \AppBundle\Entity\User */
    	$user = $this->getUser();
    	$request = $request;
    	$em = $this->getDoctrine()->getManager();
    	
    	$username = filter_var($request->get('username'), FILTER_SANITIZE_STRING);
    	if($username !== $user->getUsername()) {
    	    $user_existing = $em->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
    	     
    	    if($user_existing) {
    	    
    	        $this->get('session')
    	        ->getFlashBag()
    	        ->set('error', "Username $username is already taken.");
    	        	
    	        return $this->redirect($this->generateUrl('user_profile'));
    	    }
    	    
    	    $user->setUsername($username);
    	}
    	
    	$email = filter_var($request->get('email'), FILTER_SANITIZE_STRING);
    	if($email !== $user->getEmail()) {
    	    $user->setEmail($email);
    	}
    	
    	$resume = filter_var($request->get('resume'), FILTER_SANITIZE_STRING);
    	$faction_code = filter_var($request->get('user_faction_code'), FILTER_SANITIZE_STRING);
    	$notifAuthor = $request->get('notif_author') ? TRUE : FALSE;
    	$notifCommenter = $request->get('notif_commenter') ? TRUE : FALSE;
    	$notifMention = $request->get('notif_mention') ? TRUE : FALSE;
    	$shareDecks = $request->get('share_decks') ? TRUE : FALSE;
    	
    	$user->setFaction($faction_code);
    	$user->setResume($resume);
    	$user->setNotifAuthor($notifAuthor);
    	$user->setNotifCommenter($notifCommenter);
    	$user->setNotifMention($notifMention);
    	$user->setShareDecks($shareDecks);
    	
    	$this->get('doctrine')->getManager()->flush();
    	
        $this->get('session')
            ->getFlashBag()
            ->set('notice', "Successfully saved your profile.");
		
    	return $this->redirect($this->generateUrl('user_profile'));
    }
    
    public function infoAction(Request $request)
    {
        $jsonp = $request->query->get('jsonp');
        
        $decklist_id = $request->query->get('decklist_id');
        $card_id = $request->query->get('card_id');
        
        $content = null;
        
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
        if($user)
        {
            $user_id = $user->getId();
            
            $content = array(
                    'id' => $user_id,
                    'name' => $user->getUsername(),
                    'faction' => $user->getFaction(),
                    'donation' => $user->getDonation(),
            		'unchecked_activity' => $this->get('activity')->countUncheckedItems($this->get('activity')->getItems($user)),
            		'following' => array_map(function ($following) {
            			return $following->getId();
            		}, $user->getFollowing()->toArray())
            );
            
            if(isset($decklist_id)) {
                /* @var $em \Doctrine\ORM\EntityManager */
                $em = $this->get('doctrine')->getManager();
                /* @var $decklist \AppBundle\Entity\Decklist */
                $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
                
				if ($decklist) {
    				$decklist_id = $decklist->getId();
				    
    				$dbh = $this->get('doctrine')->getConnection();
    				
    			    $content['is_liked'] = (boolean) $dbh->executeQuery("SELECT
        				count(*)
        				from decklist d
        				join vote v on v.decklist_id=d.id
        				where v.user_id=?
        				and d.id=?", array($user_id, $decklist_id))->fetch(\PDO::FETCH_NUM)[0];
                    
    			    $content['is_favorite'] = (boolean) $dbh->executeQuery("SELECT
        				count(*)
        				from decklist d
        				join favorite f on f.decklist_id=d.id
        				where f.user_id=?
        				and d.id=?", array($user_id, $decklist_id))->fetch(\PDO::FETCH_NUM)[0];
                    
    			    $content['is_author'] = ($user_id == $decklist->getUser()->getId());
    
    			    $content['can_delete'] = ($decklist->getNbcomments() == 0) && ($decklist->getNbfavorites() == 0) && ($decklist->getNbvotes() == 0);
				}
            }
            
            if(isset($card_id)) {
                /* @var $em \Doctrine\ORM\EntityManager */
                $em = $this->get('doctrine')->getManager();
                /* @var $card \AppBundle\Entity\Card */
                $card = $em->getRepository('AppBundle:Card')->find($card_id);
                
                if($card) {
                    $reviews = $card->getReviews();
                    /* @var $review \AppBundle\Entity\Review */
                    foreach($reviews as $review) {
                        if($review->getUser()->getId() === $user->getId()) {
                            $content['review_id'] = $review->getId();
                            $content['review_text'] = $review->getRawtext();
                        }
                    }
                }
            }
        }
        $content = json_encode($content);

        $response = new Response();
        $response->setPrivate();
        if(isset($jsonp))
        {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else
        {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->setContent($content);
        
        return $response;
        
    }
	
	public function searchAction(Request $request)
	{
	    $response = new Response();
	    $response->setPublic();
	    $response->setMaxAge($this->container->getParameter('long_cache'));
	     
		$dbh = $this->get('doctrine')->getConnection();
	
		$list_packs = $this->getDoctrine()->getRepository('AppBundle:Pack')->findBy(array(), array("dateRelease" => "ASC", "position" => "ASC"));
		$packs = array();
		foreach($list_packs as $pack) {
			$packs[] = array(
					"name" => $pack->getName(),
					"code" => $pack->getCode(),
			);
		}
	
		$list_cycles = $this->getDoctrine()->getRepository('AppBundle:Cycle')->findBy(array(), array("position" => "ASC"));
		$cycles = array();
		foreach($list_cycles as $cycle) {
			$cycles[] = array(
					"name" => $cycle->getName(),
					"code" => $cycle->getCode(),
			);
		}
	
		$list_types = $this->getDoctrine()->getRepository('AppBundle:Type')->findBy(array(), array("name" => "ASC"));
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
	
		return $this->render('AppBundle:Search:searchform.html.twig', array(
		        "pagetitle" => "Card Search",
		        "pagedescription" => "Find all the cards of the game, easily searchable.",
				"packs" => $packs,
				"cycles" => $cycles,
				"types" => $types,
				"keywords" => $keywords,
				"illustrators" => $illustrators,
				"allsets" => $this->renderView('AppBundle:Default:allsets.html.twig', array(
                    "data" => $this->get('cards_data')->allsetsdata(),
		        )),
		), $response);
	}
	
	function rulesAction(Request $request)
	{
        
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge($this->container->getParameter('long_cache'));
		
		$page = $this->get('cards_data')->replaceSymbols($this->renderView('AppBundle:Default:rules.html.twig',
		        array("pagetitle" => "Rules", "pagedescription" => "Refer to the official rules of the game.")));
		
		$response->setContent($page);
		return $response;
	}
	
	function aboutAction(Request $request)
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge($this->container->getParameter('long_cache'));
		
		return $this->render('AppBundle:Default:about.html.twig', array(
		        "pagetitle" => "About",
		), $response);
	}

	function apidocAction(Request $request)
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge($this->container->getParameter('long_cache'));
		
		
		return $this->render('AppBundle:Default:apidoc.html.twig', array(
		        "pagetitle" => "API documentation",
		), $response);
	}
}

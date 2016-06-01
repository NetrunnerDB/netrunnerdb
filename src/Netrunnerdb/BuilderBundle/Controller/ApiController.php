<?php

namespace Netrunnerdb\BuilderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Netrunnerdb\BuilderBundle\Entity\Deck;
use Netrunnerdb\BuilderBundle\Entity\Deckslot;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Michelf\Markdown;
use Netrunnerdb\BuilderBundle\Entity\Decklist;
use Netrunnerdb\BuilderBundle\Entity\Decklistslot;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{


    public function getMwlAction(Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('short_cache'));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');
        if(isset($jsonp)) $jsonp = preg_replace('/^.*?([^ \(;,\-]+)$/', '$1', $jsonp);

        $locale = $request->query->get('_locale');
        if(isset($locale)) $request->setLocale($locale);

        $mwls = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbBuilderBundle:Mwl')->findBy(array(), array('dateStart' => 'DESC'));
        $data = array();
        foreach($mwls as $mwl)
        {
            $cards = array();
            foreach($mwl->getSlots() as $slot)
            {
                $cards[$slot->getCard()->getCode()] = $slot->getPenalty();
            }
            $data[] = array(
                'id' => $mwl->getId(),
                'name' => $mwl->getName(),
                'start' => $mwl->getDateStart() ? $mwl->getDateStart()->format('Y-m-d') : null,
                'cards' => $cards,
            );
        }

        $reply = array(
            'version' => '1.0',
            'total' => count($data),
            'data' => $data,
        );

        $content = json_encode($reply);

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

    public function decklistAction ($decklist_id, Request $request)
    {

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));
        $response->headers->add(array(
                'Access-Control-Allow-Origin' => '*'
        ));
        
        $jsonp = $request->query->get('jsonp');
        $locale = $request->query->get('_locale');
        if (isset($locale))
            $request->setLocale($locale);
        
        $dbh = $this->get('doctrine')->getConnection();
        $rows = $dbh->executeQuery(
                "SELECT
				d.id,
				d.date_update,
				d.name,
				d.date_creation,
				d.description,
				u.username
				from decklist d
				join user u on d.user_id=u.id
				where d.id=?
				", array(
                        $decklist_id
                ))->fetchAll();
        
        if (empty($rows)) {
            throw new NotFoundHttpException('Wrong id');
        }
        
        $decklist = $rows[0];
        $decklist['id'] = intval($decklist['id']);
        
        $lastModified = new \DateTime($decklist['ts']);
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }
        unset($decklist['ts']);
        
        $cards = $dbh->executeQuery("SELECT
				c.code card_code,
				s.quantity qty
				from decklistslot s
				join card c on s.card_id=c.id
				where s.decklist_id=?
				order by c.code asc", array(
                $decklist_id
        ))->fetchAll();
        
        $decklist['cards'] = array();
        foreach ($cards as $card) {
            $decklist['cards'][$card['card_code']] = intval($card['qty']);
        }
        
        $content = json_encode($decklist);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        $response->setContent($content);
        return $response;
    
    }
    
    public function shareddeckAction($deck_id, Request $request) {
    	$response = new Response ();
    	$response->setPublic ();
    	$response->setMaxAge ( $this->container->getParameter ( 'long_cache' ) );
    	$response->headers->add ( array (
    			'Access-Control-Allow-Origin' => '*'
    	) );
    
    	$jsonp = $this->getRequest ()->query->get ( 'jsonp' );
    	$locale = $this->getRequest ()->query->get ( '_locale' );
    	if (isset ( $locale ))
    		$this->getRequest ()->setLocale ( $locale );
    
    	$dbh = $this->get ( 'doctrine' )->getConnection ();
    	$rows = $dbh->executeQuery ( "SELECT
		d.id,
		d.date_update as ts,
		d.name,
		d.date_creation as creation,
		d.description,
		u.username
		from deck d
		join user u on d.user_id=u.id
		where u.share_decks=1 and d.id=?
		", array (
    		$deck_id
    	) )->fetchAll ();
    
    	if (empty ( $rows )) {
    		throw new NotFoundHttpException ( 'Wrong id' );
    	}
    
    	$decklist = $rows [0];
    	$decklist ['id'] = intval ( $decklist ['id'] );
    
    	$lastModified = new \DateTime ( $decklist ['ts'] );
    	$response->setLastModified ( $lastModified );
    	if ($response->isNotModified ( $this->getRequest () )) {
    		return $response;
    	}
    	unset ( $decklist ['ts'] );
    
    	$cards = $dbh->executeQuery ( "SELECT
			c.code card_code,
			s.quantity qty
			from deckslot s
			join card c on s.card_id=c.id
			where s.deck_id=?
			order by c.code asc", array (
    			$deck_id
    	) )->fetchAll ();
    
    	$decklist ['v'] = "1.0";
    	$decklist ['cards'] = array ();
    	foreach ( $cards as $card ) {
    		$decklist ['cards'] [$card ['card_code']] = intval ( $card ['qty'] );
    	}
    
    	$content = json_encode ( $decklist );
    	if (isset ( $jsonp )) {
    		$content = "$jsonp($content)";
    		$response->headers->set ( 'Content-Type', 'application/javascript' );
    	} else {
    		$response->headers->set ( 'Content-Type', 'application/json' );
    	}
    
    	$response->setContent ( $content );
    	return $response;
    }
    

    public function decklistsAction ($date, Request $request)
    {

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));
        $response->headers->add(array(
                'Access-Control-Allow-Origin' => '*'
        ));
        
        $jsonp = $request->query->get('jsonp');
        $locale = $request->query->get('_locale');
        if (isset($locale))
            $request->setLocale($locale);
        
        $dbh = $this->get('doctrine')->getConnection();
        $decklists = $dbh->executeQuery(
                "SELECT
				d.id,
				d.date_update,
				d.name,
				d.date_creation,
				d.description,
				u.username
				from decklist d
				join user u on d.user_id=u.id
				where substring(d.date_creation,1,10)=?
				", array(
                        $date
                ))->fetchAll();
        
        $lastTS = null;
        foreach ($decklists as $i => $decklist) {
            $lastTS = max($lastTS, $decklist['ts']);
            unset($decklists[$i]['ts']);
        }
        $response->setLastModified(new \DateTime($lastTS));
        if ($response->isNotModified($request)) {
            return $response;
        }
        
        foreach ($decklists as $i => $decklist) {
            $decklists[$i]['id'] = intval($decklist['id']);
            
            $cards = $dbh->executeQuery("SELECT
				c.code card_code,
				s.quantity qty
				from decklistslot s
				join card c on s.card_id=c.id
				where s.decklist_id=?
				order by c.code asc", array(
                    $decklists[$i]['id']
            ))->fetchAll();
            
            $decklists[$i]['cards'] = array();
            foreach ($cards as $card) {
                $decklists[$i]['cards'][$card['card_code']] = intval($card['qty']);
            }
        }
        
        $content = json_encode($decklists);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        $response->setContent($content);
        return $response;
    
    }

    public function decksAction (Request $request)
    {
        $response = new Response();
        $response->setPrivate();
        $response->headers->set('Content-Type', 'application/json');
        
        $locale = $request->query->get('_locale');
        if (isset($locale))
            $request->setLocale($locale);
        
        /* @var $user \Netrunnerdb\UserBundle\Entity\User */
        $user = $this->getUser();
        
        if (! $user) {
            throw new UnauthorizedHttpException("You are not logged in.");
        }
        
        $response->setContent(json_encode($this->get('decks')->getByUser($user, TRUE)));
        return $response;
    }
 
    public function saveDeckAction($deck_id, Request $request)
    {
        $response = new Response();
        $response->setPrivate();
        $response->headers->set('Content-Type', 'application/json');

        $user = $this->getUser();
        if (count($user->getDecks()) > $user->getMaxNbDecks())
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.')));
            return $response;
        }
        
        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT);
        $description = filter_var($request->get('description'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $tags = filter_var($request->get('tags'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $content = json_decode($request->get('content'), true);
        if (! count($content))
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'Cannot import empty deck')));
            return $response;
        }
        
        $em = $this->getDoctrine()->getManager();
        
        if ($deck_id) {
            $deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($deck_id);
            if ($user->getId() != $deck->getUser()->getId())
            {
                $response->setContent(json_encode(array('success' => false, 'message' => 'Wrong user')));
                return $response;
            }
        } else {
            $deck = new Deck();
        }
        
        // $content is formatted as {card_code,qty}, expected {card_code=>qty}
        $slots = array();
        foreach($content as $arr) {
            $slots[$arr['card_code']] = intval($arr['qty']);
        }
        
        $deck_id = $this->get('decks')->saveDeck($this->getUser(), $deck, $decklist_id, $name, $description, $tags, null, $slots, $deck_id ? $deck : null);
        
        if(isset($deck_id))
        {
            $response->setContent(json_encode(array('success' => true, 'message' => $this->get('decks')->getById($deck_id, TRUE))));
            return $response;
        }
        else
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'Unknown error')));
            return $response;
        }
    }
    
    public function publishAction($deck_id, Request $request)
    {
        $response = new Response();
        $response->setPrivate();
        $response->headers->set('Content-Type', 'application/json');
        
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();
        
        /* @var $deck \Netrunnerdb\BuilderBundle\Entity\Deck */
        $deck = $this->getDoctrine()
        ->getRepository('NetrunnerdbBuilderBundle:Deck')
        ->find($deck_id);
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            $response->setContent(json_encode(array('success' => false, 'message' => "You don't have access to this deck.")));
            return $response;
        }
        
        $judge = $this->get('judge');
        $analyse = $judge->analyse($deck->getSlots());
        if (is_string($analyse)) {
            $response->setContent(json_encode(array('success' => false, 'message' => $judge->problem($analyse))));
            return $response;
        }
        
        $new_content = json_encode($deck->getContent());
        $new_signature = md5($new_content);
        $old_decklists = $this->getDoctrine()
        ->getRepository('NetrunnerdbBuilderBundle:Decklist')
        ->findBy(array(
                'signature' => $new_signature
        ));
        foreach ($old_decklists as $decklist) {
            if (json_encode($decklist->getContent()) == $new_content) {
                $response->setContent(json_encode(array('success' => false, 'message' => "That decklist already exists.")));
                return $response;
            }
        }
        
        $name = filter_var($request->request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $name = substr($name, 0, 60);
        if (empty($name)) {
            $name = $deck->getName();
        }
        
        $rawdescription = trim($request->request->get('description'));
        if (empty($rawdescription)) {
            $rawdescription = $deck->getDescription();
        }
        $description = $this->get('texts')->markdown($rawdescription);
        
        $decklist = new Decklist();
        $decklist->setName($name);
        $decklist->setPrettyname(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)));
        $decklist->setRawdescription($rawdescription);
        $decklist->setDescription($description);
        $decklist->setUser($this->getUser());
        $decklist->setSignature($new_signature);
        $decklist->setIdentity($deck->getIdentity());
        $decklist->setFaction($deck->getIdentity()
                ->getFaction());
        $decklist->setSide($deck->getSide());
        $decklist->setLastPack($deck->getLastPack());
        $decklist->setNbvotes(0);
        $decklist->setNbfavorites(0);
        $decklist->setNbcomments(0);
        foreach ($deck->getSlots() as $slot) {
            $card = $slot->getCard();
            $decklistslot = new Decklistslot();
            $decklistslot->setQuantity($slot->getQuantity());
            $decklistslot->setCard($card);
            $decklistslot->setDecklist($decklist);
            $decklist->getSlots()->add($decklistslot);
        }
        if (count($deck->getChildren())) {
            $decklist->setPrecedent($deck->getChildren()[0]);
        } else
        if ($deck->getParent()) {
            $decklist->setPrecedent($deck->getParent());
        }
        $decklist->setParent($deck);
        
        $em->persist($decklist);
        $em->flush();
        
        $response->setContent(json_encode(array('success' => true, 'message' => array("id" => $decklist->getId(), "url" => $this->generateUrl('decklist_detail', array(
                'decklist_id' => $decklist->getId(),
                'decklist_name' => $decklist->getPrettyName()
        ))))));
        return $response;
        
    }
    
    public function loadDeckAction($deck_id, Request $request)
    {
        $response = new Response();
        $response->setPrivate();
        $response->headers->set('Content-Type', 'application/json');
        
        $locale = $request->query->get('_locale');
        if (isset($locale))
            $request->setLocale($locale);
        
        /* @var $user \Netrunnerdb\UserBundle\Entity\User */
        $user = $this->getUser();
        
        if (! $user) {
            throw new UnauthorizedHttpException("You are not logged in.");
        }
        
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();
        
        $deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($deck_id);
        if ($user->getId() != $deck->getUser()->getId())
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'Wrong user')));
            return $response;
        }
        
        $deck = $this->get('decks')->getById($deck_id, TRUE);
        $response->setContent(json_encode($deck));
        return $response;
    }
    
}

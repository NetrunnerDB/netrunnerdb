<?php

namespace Netrunnerdb\BuilderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\FOSRestController;

class PublicApi20Controller extends FOSRestController
{
	private function prepareResponse(array $entities)
	{
		$response = new JsonResponse();
		$response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$response->setPublic();
		$response->headers->add(array('Access-Control-Allow-Origin' => '*'));
		
		$content = [ 'version_number' => '2.0' ];
		
		$dateUpdate = array_reduce($entities, function($carry, $item) {
			if($carry || $item->getDateUpdate() > $carry) return $item->getDateUpdate();
			else return $carry;
		});
		
		$response->setLastModified($dateUpdate);
		if($response->isNotModified($this->getRequest())) {
			return $response;
		}
		
		$content['data'] = array_map(function ($entity) {
			return $entity->serialize();
		}, $entities);
		
		$content['total'] = count($content['data']);
		
		$content['success'] = TRUE;
		
		$response->setData($content);
		
		return $response;
	}

	/**
	 * Get a cycle
	 *
	 * @ApiDoc(
	 *  section="Cycle",
	 *  resource=true,
	 *  description="Get one cycle",
	 *  parameters={
	 *  },
	 * )
	 */
	public function cycleAction($cycle_code)
	{
		$cycle = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbCardsBundle:Cycle')->findOneBy(['code' => $cycle_code]);
	
		if(!$cycle) {
			throw $this->createNotFoundException("Cycle not found");
		}
		
		return $this->prepareResponse([$cycle]);
	}
	
	/**
	 * Get all the cycles
	 *
	 * @ApiDoc(
	 *  section="Cycle",
	 *  resource=true,
	 *  description="Get all the cycles",
	 *  parameters={
	 *  },
	 * )
	 */
	public function cyclesAction()
	{
		$data = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbCardsBundle:Cycle')->findAll();
		
		return $this->prepareResponse($data);
	}

	/**
	 * Get a pack
	 *
	 * @ApiDoc(
	 *  section="Pack",
	 *  resource=true,
	 *  description="Get one pack",
	 *  parameters={
	 *  },
	 * )
	 */
	public function packAction($pack_code)
	{
		$pack = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbCardsBundle:Pack')->findOneBy(['code' => $pack_code]);
	
		if(!$pack) {
			throw $this->createNotFoundException("Pack not found");
		}
		
		return $this->prepareResponse([$pack]);
	}
	
	/**
	 * Get all the packs as an array of JSON objects.
	 *
	 * @ApiDoc(
	 *  section="Pack",
	 *  resource=true,
	 *  description="Get all the packs",
	 *  parameters={
	 *  },
	 * )
	 */
	public function packsAction()
	{
		$data = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbCardsBundle:Pack')->findAll();
	
		return $this->prepareResponse($data);
	}

	/**
	 * Get a card
	 *
	 * @ApiDoc(
	 *  section="Card",
	 *  resource=true,
	 *  description="Get one card",
	 *  parameters={
	 *  },
	 * )
	 */
	public function cardAction($card_code)
	{
		$card = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(['code' => $card_code]);
	
		if(!$card) {
			throw $this->createNotFoundException("Card not found");
		}
		
		return $this->prepareResponse([$card]);
	}
	
	/**
	 * Get all the cards as an array of JSON objects.
	 *
	 * @ApiDoc(
	 *  section="Card",
	 *  resource=true,
	 *  description="Get all the cards",
	 *  parameters={
	 *  },
	 * )
	 */
	public function cardsAction()
	{
		$data = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbCardsBundle:Card')->findAll();
		
		return $this->prepareResponse($data);
	}

	/**
	 * Get a decklist
	 *
	 * @ApiDoc(
	 *  section="Decklist",
	 *  resource=true,
	 *  description="Get one (published) decklist",
	 *  parameters={
	 *  },
	 * )
	 */
	public function decklistAction($decklist_id)
	{
		$decklist = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbBuilderBundle:Decklist')->find($decklist_id);
	
		if(!$decklist) {
			throw $this->createNotFoundException("Decklist not found");
		}
		
		return $this->prepareResponse([$decklist]);
	}

	/**
	 * Get all the decklists for a date
	 *
	 * @ApiDoc(
	 *  section="Decklist",
	 *  resource=true,
	 *  description="Get all the (published) decklists for a date",
	 *  parameters={
	 *  },
	 * )
	 */
	public function decklistsByDateAction($date)
	{
		$date_from = new \DateTime($date);
		$date_to = clone($date_from);
		$date_to->modify('+1 day');
		
		$qb = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbBuilderBundle:Decklist')->createQueryBuilder('d');
		$qb->where($qb->expr()->between('d.dateCreation', ':date_from', ':date_to'));
		$qb->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATETIME);
		$qb->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATETIME);
		
		$data = $qb->getQuery()->execute();
	
		return $this->prepareResponse($data);
	}

	/**
	 * Get a deck
	 *
	 * @ApiDoc(
	 *  section="Deck",
	 *  resource=true,
	 *  description="Get one (private, shared) deck",
	 *  parameters={
	 *  },
	 * )
	 */
	public function deckAction($deck_id)
	{
		/* @var $deck \Netrunnerdb\BuilderBundle\Entity\Deck */
		$deck = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbBuilderBundle:Deck')->find($deck_id);
	
		if(!$deck) {
			throw $this->createNotFoundException("Deck not found");
		}
		
		if(!$deck->getUser()->getShareDecks()) {
			throw $this->createAccessDeniedException("Deck not shared");
		}
		
		return $this->prepareResponse([$deck]);
	}

	/**
	 * Get all MWL data
	 *
	 * @ApiDoc(
	 *  section="MWL",
	 *  resource=true,
	 *  description="Get all the mwl data",
	 *  parameters={
	 *  },
	 * )
	 */
	public function mwlAction()
	{
		$data = $this->getDoctrine()->getManager()->getRepository('NetrunnerdbBuilderBundle:Mwl')->findAll();
	
		return $this->prepareResponse($data);
	}
}

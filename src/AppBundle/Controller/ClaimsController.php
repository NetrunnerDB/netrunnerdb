<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Claim;
use AppBundle\Entity\Decklist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * API Controller for Claims (decklist ranking in tournaments)
 *
 * @author cbertolini
 * @Route("/decklists/{decklist_id}/claims")
 */
class ClaimsController extends AbstractOauthController
{

    /**
     * Create a claim
     * @param Request $request
     * @Route("")
     * @Method("POST")
     */
    public function postAction ($decklist_id, Request $request)
    {
        $client = $this->getOauthClient($request);
        if(!$client) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        /* @var $decklist Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist) {
            throw $this->createNotFoundException();
        }
        /* @var $claim Claim */
        $claim = $this->parseJsonRequest($request, 'AppBundle\Entity\Claim');
        $claim->setDecklist($decklist);
        $claim->setClient($client);
        $em->persist($claim);
        $em->flush();

        $jsend = $this->getJsendResponse('success', ['claim' => $claim]);
        $url = $this->generateUrl('app_claims_get', ['decklist_id' => $decklist_id, 'id' => $claim->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->createJsonResponse($jsend, 201, ['Location' => $url]);
    }

    /**
     * 
     * @param integer $decklist_id
     * @param integer $id
     * @param Request $request
     * @return Claim
     * @throws \Exception
     */
    protected function retrieveClaim ($decklist_id, $id, Request $request)
    {
        $client = $this->getOauthClient($request);
        if(!$client) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        /* @var $decklist Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist) {
            throw $this->createNotFoundException();
        }
        /* @var $claim Claim */
        $claim = $em->getRepository('AppBundle:Claim')->find($id);
        if(!$claim) {
            throw $this->createNotFoundException();
        }
        if($claim->getDecklist()->getId() !== $decklist->getId()) {
            throw $this->createNotFoundException();
        }
        if($claim->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $claim;
    }

    /**
     * Return a claim
     * @param integer $id
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction ($decklist_id, $id, Request $request)
    {
        $claim = $this->retrieveClaim($decklist_id, $id, $request);
        $jsend = $this->getJsendResponse('success', ['claim' => $claim]);
        return $this->createJsonResponse($jsend);
    }

    /**
     * Update a claim
     * @param integer $id
     * @Route("/{id}")
     * @Method("PUT")
     */
    public function putAction ($decklist_id, $id, Request $request)
    {
        $claim = $this->retrieveClaim($decklist_id, $id, $request);
        /* @var $updatingClaim Claim */
        $updatingClaim = $this->parseJsonRequest($request, 'AppBundle\Entity\Claim');
        $claim->setName($updatingClaim->getName());
        $claim->setRank($updatingClaim->getRank());
        $claim->setUrl($updatingClaim->getUrl());
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $jsend = $this->getJsendResponse('success', ['claim' => $claim]);

        return $this->createJsonResponse($jsend);
    }

    /**
     * Delete a claim
     * @param integer $id
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction ($decklist_id, $id, Request $request)
    {
        $claim = $this->retrieveClaim($decklist_id, $id, $request);
        $em = $this->getDoctrine()->getManager();
        $em->remove($claim);
        $em->flush();

        $jsend = $this->getJsendResponse('success', null);

        return $this->createJsonResponse($jsend);
    }

}

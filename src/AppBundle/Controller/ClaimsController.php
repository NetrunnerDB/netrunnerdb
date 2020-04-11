<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Claim;
use AppBundle\Entity\Client;
use AppBundle\Entity\Decklist;
use Doctrine\ORM\EntityManagerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * API Controller for Claims (decklist ranking in tournaments)
 *
 * @author cbertolini
 * @Route("/api/2.1/private/decklists/{decklist_id}/claims")
 */
class ClaimsController extends AbstractOauthController
{
    private function deserializeClaim(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException("Malformed JSON");
        }

        /** @var Claim $claim */
        $claim = $this->arrayTransformer->fromArray($data, 'AppBundle\Entity\Claim');

        return $claim;
    }

    /**
     * Create a claim
     *
     * Example body request:
     * {
     *   "name":"Cheltenham - Proud Lion - SC",
     *   "url":"https://alwaysberunning.net/tournaments/300/cheltenham-proud-lion-sc",
     *   "rank":1,
     *   "participants":32
     * }
     *
     * @param Request $request
     * @Route("")
     * @Method("POST")
     */
    public function postAction(int $decklist_id, Request $request, EntityManagerInterface $entityManager)
    {
        $client = $this->getOauthClient();
        if (!$client instanceof Client) {
            throw $this->createAccessDeniedException();
        }

        /** @var Decklist $decklist */
        $decklist = $entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
        if (!$decklist) {
            throw $this->createNotFoundException();
        }
        /** @var Claim $claim */
        $claim = $this->deserializeClaim($request);
        $claim->setDecklist($decklist);
        $claim->setClient($client);
        $claim->setUser($this->getUser());
        $entityManager->persist($claim);
        $entityManager->flush();

        $jsend = $this->getJsendResponse('success', ['claim' => $claim]);
        $url = $this->generateUrl('app_claims_get', ['decklist_id' => $decklist_id, 'id' => $claim->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->createJsonResponse($jsend, 201, ['Location' => $url]);
    }

    /**
     * @param int $decklist_id
     * @param int $id
     * @return Claim
     */
    protected function retrieveClaim(int $decklist_id, int $id, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $client = $this->getOauthClient();
        if (!$client instanceof Client) {
            throw $this->createAccessDeniedException();
        }

        /** @var Decklist $decklist */
        $decklist = $entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
        if (!$decklist) {
            throw $this->createNotFoundException();
        }
        /** @var Claim|null $claim */
        $claim = $entityManager->getRepository('AppBundle:Claim')->find($id);
        if (!$claim instanceof Claim) {
            throw $this->createNotFoundException();
        }
        if ($claim->getDecklist()->getId() !== $decklist->getId()) {
            throw $this->createNotFoundException();
        }
        if ($claim->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException();
        }
        if ($claim->getUser()->getId() !== $user->getId()) {
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
    public function getAction(int $decklist_id, $id, EntityManagerInterface $entityManager)
    {
        $claim = $this->retrieveClaim($decklist_id, $id, $entityManager);
        $jsend = $this->getJsendResponse('success', ['claim' => $claim]);
        return $this->createJsonResponse($jsend);
    }

    /**
     * Update a claim
     * @param integer $id
     * @Route("/{id}")
     * @Method("PUT")
     */
    public function putAction(int $decklist_id, int $id, Request $request, EntityManagerInterface $entityManager)
    {
        $client = $this->getOauthClient();
        if (!$client instanceof Client) {
            throw $this->createAccessDeniedException();
        }

        $claim = $this->retrieveClaim($decklist_id, $id, $entityManager);
        /** @var Claim $updatingClaim */
        $updatingClaim = $this->deserializeClaim($request);
        $claim->setName($updatingClaim->getName());
        $claim->setRank($updatingClaim->getRank());
        $claim->setParticipants($updatingClaim->getParticipants());
        $claim->setUrl($updatingClaim->getUrl());

        $entityManager->flush();

        $jsend = $this->getJsendResponse('success', ['claim' => $claim]);

        return $this->createJsonResponse($jsend);
    }

    /**
     * Delete a claim
     * @param integer $id
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(int $decklist_id, int $id, EntityManagerInterface $entityManager)
    {
        $client = $this->getOauthClient();
        if (!$client instanceof Client) {
            throw $this->createAccessDeniedException();
        }

        $claim = $this->retrieveClaim($decklist_id, $id, $entityManager);

        $entityManager->remove($claim);
        $entityManager->flush();

        $jsend = $this->getJsendResponse('success');

        return $this->createJsonResponse($jsend);
    }
}

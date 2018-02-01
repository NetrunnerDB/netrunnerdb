<?php

namespace AppBundle\Controller;

use AppBundle\Service\Decks;
use AppBundle\Service\Judge;
use AppBundle\Service\RotationService;
use AppBundle\Service\Texts;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\FOSRestController;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Decklistslot;

class PrivateApi20Controller extends FOSRestController
{
    private function prepareResponse(array $data)
    {
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->setPrivate();
        
        $content = [ 'version_number' => '2.0' ];
        
        $content['data'] = array_map(function ($entity) {
            return (is_object($entity) && $entity instanceof \Serializable) ? $entity->serialize() : $entity;
        }, $data);
        
        $content['total'] = count($content['data']);
        
        $content['success'] = true;
        
        $response->setData($content);
        
        return $response;
    }
    
    private function prepareFailedResponse($msg)
    {
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->setPrivate();
        
        $content = [ 'version_number' => '2.0' ];
        
        $content['success'] = false;
        
        $content['msg'] = $msg;
        
        $response->setData($content);
        
        return $response;
    }

    /**
     * Get one deck
     *
     * @ApiDoc(
     *  section="Deck",
     *  resource=true,
     *  description="Get one (private) deck of authenticated user",
     *  parameters={
     *    {"name"="include_history", "dataType"="boolean", "required"=false, "description"="truthy value (eg '1') to include the deck changes"},
     *  }
     * )
     */
    public function loadDeckAction($deck_id, Request $request)
    {
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
        
        $includeHistory = $request->query->has('include_history') && $request->query->get('include_history');
        
        $deck = $this->getDoctrine()->getManager()->getRepository('AppBundle:Deck')->findOneBy(['user' => $user, 'id' => $deck_id]);
        
        if (!$deck) {
            throw $this->createNotFoundException("Deck not found");
        }
        
        $history = [];
        
        if ($includeHistory) {
            $qb = $this->getDoctrine()->getManager()->getRepository('AppBundle:Deckchange')->createQueryBuilder('h');
            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb->select('h')
                ->from('AppBundle:Deckchange', 'h')
                ->where('h.deck = :deck')
                ->andWhere('h.saved = :saved')
                ->orderBy('h.dateCreation', 'DESC')
                ->setParameter('deck', $deck)
                ->setParameter('saved', true);
            $query = $qb->getQuery();
            $result = $query->getResult();
            
            foreach ($result as $deckchange) {
                /* @var $deckchange \AppBundle\Entity\Deckchange */
                $variation = json_decode($deckchange->getVariation(), true);
                $changes = [];
                foreach ($variation[0] as $card_code => $addition) {
                    $changes[$card_code] = $addition;
                }
                foreach ($variation[1] as $card_code => $substraction) {
                    $changes[$card_code] = - $substraction;
                }
                $history[$deckchange->getDatecreation()->format('c')] = $changes;
            }
        }
        
        $data = $deck->serialize();
        $data['history'] = $history;
        
        return $this->prepareResponse([$data]);
    }

    /**
     * Save one deck
     *
     * @ApiDoc(
     *  section="Deck",
     *  resource=true,
     *  description="Save one (private) deck of authenticated user",
     *  parameters={
     *    {"name"="deck_id", "dataType"="integer", "required"=true, "description"="ID"},
     *    {"name"="name", "dataType"="string", "required"=true, "description"="Name"},
     *    {"name"="description", "dataType"="string", "required"=false, "description"="Description of the deck in Markdown"},
     *    {"name"="tags", "dataType"="string", "required"=true, "description"="Comma-separated list of tags"},
     *    {"name"="decklist_id", "dataType"="integer", "required"=false, "description"="ID of the parent decklist"}
     *  },
     * )
     */
    public function saveDeckAction(Request $request)
    {
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
        
        $requestJsonBody = $request->getContent();
        $requestContent = json_decode($requestJsonBody, true);
        $last_error = json_last_error();
        
        if ($last_error) {
            return $this->prepareFailedResponse("Request body is not proper JSON [error code $last_error].");
        }
        
        if (!key_exists('deck_id', $requestContent)) {
            return $this->prepareFailedResponse("Missing deck_id parameter.");
        }
        
        $deck_id = $requestContent['deck_id'];
        
        if ($deck_id) {
            $deck = $this->getDoctrine()->getManager()->getRepository('AppBundle:Deck')->findOneBy(['user' => $user, 'id' => $deck_id]);
            if (!$deck) {
                throw $this->createNotFoundException("Deck not found");
            }
        } else {
            if (count($user->getDecks()) >= $user->getMaxNbDecks()) {
                return $this->prepareFailedResponse("You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.");
            }
            $deck = new Deck();
        }
        
        $name = filter_var($requestContent['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = isset($requestContent['decklist_id']) ? filter_var($requestContent['decklist_id'], FILTER_SANITIZE_NUMBER_INT) : null;
        $description = isset($requestContent['description']) ? filter_var($requestContent['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : '';
        $tags = isset($requestContent['tags']) ? filter_var($requestContent['tags'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : '';
        $content = $requestContent['content'];
        
        if (!$name) {
            return $this->prepareFailedResponse("Missing parameter 'name'.");
        }
        if (!$content) {
            return $this->prepareFailedResponse("Missing parameter 'content'.");
        }
        if (!count($content)) {
            return $this->prepareFailedResponse("Empty parameter 'content'.");
        }
        
        $deck_id = $this->get(Decks::class)->saveDeck($user, $deck, $decklist_id, $name, $description, $tags, null, $content, $deck_id ? $deck : null);
        
        if (isset($deck_id)) {
            return $this->prepareResponse([$deck]);
        } else {
            return $this->prepareFailedResponse("Unknown error.");
        }
    }

    /**
     * Publish one deck
     *
     * @ApiDoc(
     *  section="Deck",
     *  resource=true,
     *  description="Publish one (private) deck of authenticated user",
     *  parameters={
     *    {"name"="deck_id", "dataType"="integer", "required"=true, "description"="ID"},
     *    {"name"="name", "dataType"="string", "required"=false, "description"="Name. Taken from the deck is absent/empty"},
     *    {"name"="description", "dataType"="string", "required"=false, "description"="Description in Markdown. Taken from the deck if absent/empty"}
     *  },
     * )
     */
    public function publishDeckAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
    
        $requestJsonBody = $request->getContent();
        $requestContent = json_decode($requestJsonBody, true);
        $last_error = json_last_error();
    
        if ($last_error) {
            return $this->prepareFailedResponse("Request body is not proper JSON [error code $last_error].");
        }
    
        if (!key_exists('deck_id', $requestContent)) {
            return $this->prepareFailedResponse("Missing deck_id parameter.");
        }
    
        $deck_id = $requestContent['deck_id'];
        $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(['user' => $user, 'id' => $deck_id]);
        if (!$deck) {
            throw $this->createNotFoundException("Deck not found");
        }
        
        $judge = $this->get(Judge::class);
        $analyse = $judge->analyse($deck->getSlots());
        if (is_string($analyse)) {
            return $this->prepareFailedResponse($judge->problem($analyse));
        }
        
        $new_content = json_encode($deck->getContent());
        $new_signature = md5($new_content);
        $old_decklists = $entityManager->getRepository('AppBundle:Decklist')->findBy(['signature' => $new_signature]);
        foreach ($old_decklists as $decklist) {
            if (json_encode($decklist->getContent()) == $new_content) {
                return $this->prepareFailedResponse("A decklist with this content already exists.");
            }
        }
        
        $name = isset($requestContent['name']) ? filter_var($requestContent['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : '';
        if (empty($name)) {
            $name = $deck->getName();
        }
        $name = substr($name, 0, 60);
        
        $rawdescription = isset($requestContent['description']) ? trim($requestContent['description']) : '';
        if (empty($rawdescription)) {
            $rawdescription = $deck->getDescription();
        }
        $description = $this->get(Texts::class)->markdown($rawdescription);
        
        $decklist = new Decklist();
        $decklist->setName($name);
        $decklist->setPrettyname(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)));
        $decklist->setRawdescription($rawdescription);
        $decklist->setDescription($description);
        $decklist->setUser($user);
        $decklist->setSignature($new_signature);
        $decklist->setIdentity($deck->getIdentity());
        $decklist->setFaction($deck->getIdentity()->getFaction());
        $decklist->setSide($deck->getSide());
        $decklist->setLastPack($deck->getLastPack());
        $decklist->setNbvotes(0);
        $decklist->setNbfavorites(0);
        $decklist->setNbcomments(0);
        $decklist->setDotw(0);
        $decklist->setModerationStatus(Decklist::MODERATION_PUBLISHED);
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
        } else {
            if ($deck->getParent()) {
                $decklist->setPrecedent($deck->getParent());
            }
        }
        $decklist->setParent($deck);
        $decklist->setRotation($this->get(RotationService::class)->findCompatibleRotation($decklist));

        $entityManager->persist($decklist);
        $entityManager->flush();
        
        return $this->prepareResponse([$decklist]);
    }
    
    /**
     * Get all decks
     *
     * @ApiDoc(
     *  section="Deck",
     *  resource=true,
     *  description="Get all (private) decks of authenticated user",
     *  parameters={
     *  },
     * )
     */
    public function decksAction(Request $request)
    {
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
    
        $data = $this->getDoctrine()->getManager()->getRepository('AppBundle:Deck')->findBy(['user' => $user]);
    
        return $this->prepareResponse($data);
    }

    /**
     * Get all decklists
     *
     * @ApiDoc(
     *  section="Decklist",
     *  resource=true,
     *  description="Get all (published) decklists created by authenticated user",
     *  parameters={
     *  },
     * )
     */
    public function decklistsAction(Request $request)
    {
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
    
        $data = $this->getDoctrine()->getManager()->getRepository('AppBundle:Decklist')->findBy(['user' => $user]);
    
        return $this->prepareResponse($data);
    }
    
    /**
     * Get account info
     *
     * @ApiDoc(
     *  section="Account",
     *  resource=true,
     *  description="Get the account info of authenticated user",
     *  parameters={
     *  },
     * )
     */
    public function accountInfoAction(Request $request)
    {
        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
    
        $info = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'reputation' => $user->getReputation(),
                'sharing' => $user->getShareDecks()
        ];
    
        return $this->prepareResponse([$info]);
    }
}

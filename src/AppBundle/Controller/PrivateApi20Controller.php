<?php

namespace AppBundle\Controller;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Entity\Deckchange;
use AppBundle\Entity\User;
use AppBundle\Service\DeckManager;
use AppBundle\Service\Judge;
use AppBundle\Service\RotationService;
use AppBundle\Service\TextProcessor;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
            return (is_object($entity) && $entity instanceof NormalizableInterface) ? $entity->normalize() : $entity;
        }, $data);
        
        $content['total'] = count($data);
        
        $content['success'] = true;
        
        $response->setData($content);
        
        return $response;
    }
    
    private function prepareFailedResponse(string $msg)
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
    public function loadDeckAction(int $deck_id, Request $request, EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        
        $includeHistory = $request->query->has('include_history') && $request->query->get('include_history');

        /** @var Deck $deck */
        $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(['user' => $user, 'id' => $deck_id]);
        
        if (!$deck) {
            throw $this->createNotFoundException();
        }
        
        $history = [];
        
        if ($includeHistory) {
            $qb = $entityManager->createQueryBuilder();
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
                /** @var Deckchange $deckchange */
                $variation = json_decode($deckchange->getVariation(), true);
                $changes = [];
                foreach ($variation[0] as $card_code => $addition) {
                    $changes[$card_code] = $addition;
                }
                foreach ($variation[1] as $card_code => $substraction) {
                    $changes[$card_code] = - $substraction;
                }
                $history[$deckchange->getDateCreation()->format('c')] = $changes;
            }
        }
        
        $data = $deck->normalize();
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
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function saveDeckAction(Request $request, EntityManagerInterface $entityManager, DeckManager $deckManager)
    {
        $user = $this->getUser();

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
            $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(['user' => $user, 'id' => $deck_id]);
            if (!$deck) {
                throw $this->createNotFoundException();
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

        if ($deck instanceof Deck) {
            $deck_id = $deckManager->saveDeck($user, $deck, $decklist_id, $name, $description, $tags, null, $content, $deck_id ? $deck : null);
        }

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
    public function publishDeckAction(Request $request, EntityManagerInterface $entityManager, Judge $judge, TextProcessor $textProcessor, RotationService $rotationService)
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException();
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
        if (!$deck instanceof Deck) {
            throw $this->createNotFoundException();
        }
        
        $analyse = $judge->analyse($deck->getSlots()->toArray());
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
        $description = $textProcessor->markdown($rawdescription);
        
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
        $decklist->setRotation($rotationService->findCompatibleRotation($decklist));

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
    public function decksAction(EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
    
        $data = $entityManager->getRepository('AppBundle:Deck')->findBy(['user' => $user]);
    
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
    public function decklistsAction(EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
    
        $data = $entityManager->getRepository('AppBundle:Decklist')->findBy(['user' => $user]);
    
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
    public function accountInfoAction()
    {
        /** @var User $user */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException();
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

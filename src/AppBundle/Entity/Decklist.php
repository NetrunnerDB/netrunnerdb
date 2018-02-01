<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Decklist
 */
class Decklist implements NormalizableInterface, TimestampableInterface
{
    const MODERATION_PUBLISHED = 0;
    const MODERATION_RESTORED = 1;
    const MODERATION_TRASHED = 2;
    const MODERATION_DELETED = 3;

    public function __toString()
    {
        return "[$this->id] $this->name";
    }

    public function normalize()
    {
        $cards = [];
        foreach ($this->slots as $slot) {
            $cards[$slot->getCard()->getCode()] = $slot->getQuantity();
        }

        return [
            'id'               => $this->id,
            'date_creation'    => $this->dateCreation->format('c'),
            'date_update'      => $this->dateUpdate->format('c'),
            'name'             => $this->name,
            'description'      => $this->description,
            'user_id'          => $this->user->getId(),
            'user_name'        => $this->user->getUsername(),
            'tournament_badge' => $this->tournament ? true : false,
            'cards'            => $cards,
            'mwl_code'         => $this->mwl ? $this->mwl->getCode() : null,
        ];
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $prettyname;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $rawdescription;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var integer
     */
    private $nbvotes;

    /**
     * @var integer
     */
    private $nbfavorites;

    /**
     * @var integer
     */
    private $nbcomments;

    /**
     * @var integer
     */
    private $dotw;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Side
     */
    private $side;

    /**
     * @var Card|null
     */
    private $identity;

    /**
     * @var Faction
     */
    private $faction;

    /**
     * @var Pack
     */
    private $lastPack;

    /**
     * @var Collection|Decklistslot[]
     */
    private $slots;

    /**
     * @var Collection|Comment[]
     */
    private $comments;

    /**
     * @var Collection|User[]
     */
    private $favorites;

    /**
     * @var Collection|User[]
     */
    private $votes;

    /**
     * @var Rotation
     */
    private $rotation;

    /**
     * @var integer
     */
    private $moderationStatus;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->isLegal = true;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return Decklist
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set prettyname
     *
     * @param string $prettyname
     * @return $this
     */
    public function setPrettyname($prettyname)
    {
        $this->prettyname = $prettyname;

        return $this;
    }

    /**
     * Get prettyname
     *
     * @return string
     */
    public function getPrettyname()
    {
        return $this->prettyname;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set rawdescription
     *
     * @param string $rawdescription
     * @return $this
     */
    public function setRawdescription($rawdescription)
    {
        $this->rawdescription = $rawdescription;

        return $this;
    }

    /**
     * Get rawdescription
     *
     * @return string
     */
    public function getRawdescription()
    {
        return $this->rawdescription;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return $this
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return Decklist
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param int $nbvotes
     * @return $this
     */
    public function setNbvotes(int $nbvotes)
    {
        $this->nbvotes = $nbvotes;

        return $this;
    }

    /**
     * @return int
     */
    public function getNbvotes()
    {
        return $this->nbvotes;
    }

    /**
     * @param int $nbfavorites
     * @return $this
     */
    public function setNbfavorites(int $nbfavorites)
    {
        $this->nbfavorites = $nbfavorites;

        return $this;
    }

    /**
     * Get nbfavorites
     *
     * @return int
     */
    public function getNbfavorites()
    {
        return $this->nbfavorites;
    }

    /**
     * @param int $nbcomments
     * @return $this
     */
    public function setNbcomments(int $nbcomments)
    {
        $this->nbcomments = $nbcomments;

        return $this;
    }

    /**
     * @return int
     */
    public function getNbcomments()
    {
        return $this->nbcomments;
    }

    /**
     * @param int $dotw
     * @return $this
     */
    public function setDotw(int $dotw)
    {
        $this->dotw = $dotw;

        return $this;
    }

    /**
     * @return int
     */
    public function getDotw()
    {
        return $this->dotw;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set side
     *
     * @param Side $side
     * @return $this
     */
    public function setSide(Side $side = null)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get side
     *
     * @return Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set identity
     *
     * @param Card $identity
     * @return $this
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return Card|null
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return Decklistslot[]|Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Get cards
     *
     * @return Card[]
     */
    public function getCards()
    {
        $arr = [];
        foreach ($this->slots as $slot) {
            $card = $slot->getCard();
            $arr[$card->getCode()] = ['qty' => $slot->getQuantity(), 'card' => $card];
        }

        return $arr;
    }

    /**
     * Set lastPack
     *
     * @param Pack $lastPack
     * @return $this
     */
    public function setLastPack($lastPack)
    {
        $this->lastPack = $lastPack;

        return $this;
    }

    /**
     * Get lastPack
     *
     * @return Pack
     */
    public function getLastPack()
    {
        return $this->lastPack;
    }

    /**
     * Set faction
     *
     * @param Faction $faction
     * @return $this
     */
    public function setFaction($faction)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @return Comment[]|Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add favorite
     *
     * @param User $user
     * @return Decklist
     */
    public function addFavorite($user)
    {
        $this->favorites[] = $user;

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * Add vote
     *
     * @param User $user
     * @return Decklist
     */
    public function addVote($user)
    {
        $this->votes[] = $user;

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    public function getContent()
    {
        $arr = [];
        foreach ($this->slots as $slot) {
            $arr[$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        ksort($arr);

        return $arr;
    }
    /*
    public function getPrettyName()
    {
        return preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($this->name));
    }
    */
    /**
     * Add slots
     *
     * @param Decklistslot $slots
     * @return Decklist
     */
    public function addSlot(Decklistslot $slots)
    {
        $this->slots[] = $slots;

        return $this;
    }

    /**
     * Remove slots
     *
     * @param Decklistslot $slots
     */
    public function removeSlot(Decklistslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * Add comments
     *
     * @param Comment $comments
     * @return Decklist
     */
    public function addComment(Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param Comment $comments
     */
    public function removeComment(Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Remove favorites
     *
     * @param User $favorites
     */
    public function removeFavorite(User $favorites)
    {
        $this->favorites->removeElement($favorites);
    }

    /**
     * Remove votes
     *
     * @param User $votes
     */
    public function removeVote(User $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * @var Deck
     */
    private $parent;


    /**
     * Set parent
     *
     * @param Deck $parent
     * @return Decklist
     */
    public function setParent(Deck $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Deck
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @var Collection
     */
    private $successors;

    /**
     * @var Decklist
     */
    private $precedent;


    /**
     * Add successors
     *
     * @param Decklist $successors
     * @return Decklist
     */
    public function addSuccessor(Decklist $successors)
    {
        $this->successors[] = $successors;

        return $this;
    }

    /**
     * Remove successors
     *
     * @param Decklist $successors
     */
    public function removeSuccessor(Decklist $successors)
    {
        $this->successors->removeElement($successors);
    }

    /**
     * Get successors
     *
     * @return Collection
     */
    public function getSuccessors()
    {
        return $this->successors;
    }

    /**
     * Set precedent
     *
     * @param Decklist $precedent
     * @return Decklist
     */
    public function setPrecedent(Decklist $precedent = null)
    {
        $this->precedent = $precedent;

        return $this;
    }

    /**
     * Get precedent
     *
     * @return Decklist
     */
    public function getPrecedent()
    {
        return $this->precedent;
    }

    /**
     * @var Collection
     */
    private $children;


    /**
     * Add children
     *
     * @param Deck $children
     * @return Decklist
     */
    public function addChildren(Deck $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Deck $children
     */
    public function removeChildren(Deck $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @var Tournament
     */
    private $tournament;


    /**
     * Add children
     *
     * @param Deck $children
     * @return Decklist
     */
    public function addChild(Deck $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Deck $children
     */
    public function removeChild(Deck $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Set tournament
     *
     * @param Tournament $tournament
     * @return Decklist
     */
    public function setTournament(Tournament $tournament = null)
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * Get tournament
     *
     * @return Tournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * @var Collection
     */
    private $legalities;


    /**
     * Add legality
     *
     * @param Legality $legality
     *
     * @return Decklist
     */
    public function addLegality(Legality $legality)
    {
        $this->legalities[] = $legality;

        return $this;
    }

    /**
     * Remove legality
     *
     * @param Legality $legality
     */
    public function removeLegality(Legality $legality)
    {
        $this->legalities->removeElement($legality);
    }

    /**
     * Get legalities
     *
     * @return Collection
     */
    public function getLegalities()
    {
        return $this->legalities;
    }

    /**
     * Set moderationStatus
     *
     * @param integer $moderationStatus
     *
     * @return Decklist
     */
    public function setModerationStatus($moderationStatus)
    {
        $this->moderationStatus = $moderationStatus;

        return $this;
    }

    /**
     * Get moderationStatus
     *
     * @return integer
     */
    public function getModerationStatus()
    {
        return $this->moderationStatus;
    }

    /**
     * @var Modflag
     */
    private $modflag;


    /**
     * Set modflag
     *
     * @param Modflag $modflag
     *
     * @return Decklist
     */
    public function setModflag(Modflag $modflag = null)
    {
        $this->modflag = $modflag;

        return $this;
    }

    /**
     * Get modflag
     *
     * @return Modflag
     */
    public function getModflag()
    {
        return $this->modflag;
    }

    /**
     * @var Collection
     */
    private $claims;


    /**
     * Add claim
     *
     * @param Claim $claim
     *
     * @return Decklist
     */
    public function addClaim(Claim $claim)
    {
        $this->claims[] = $claim;

        return $this;
    }

    /**
     * Remove claim
     *
     * @param Claim $claim
     */
    public function removeClaim(Claim $claim)
    {
        $this->claims->removeElement($claim);
    }

    /**
     * Get claims
     *
     * @return Collection
     */
    public function getClaims()
    {
        return $this->claims;
    }

    private $isLegal;

    public function getIsLegal()
    {
        return $this->isLegal;
    }

    public function setIsLegal($isLegal)
    {
        $this->isLegal = $isLegal;
    }

    public function getRotation()
    {
        return $this->rotation;
    }

    public function setRotation($rotation)
    {
        $this->rotation = $rotation;

        return $this;
    }

    /**
     * @var Mwl
     */
    private $mwl;


    /**
     * Set mwl
     *
     * @param Mwl $mwl
     *
     * @return Decklist
     */
    public function setMwl(Mwl $mwl = null)
    {
        $this->mwl = $mwl;

        return $this;
    }

    /**
     * Get mwl
     *
     * @return Mwl
     */
    public function getMwl()
    {
        return $this->mwl;
    }
}

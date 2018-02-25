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
     * @var Card
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
     * @var Rotation|null
     */
    private $rotation;

    /**
     * @var integer
     */
    private $moderationStatus;

    /**
     * @var Deck|null
     */
    private $parent;

    /**
     * @var Collection
     */
    private $successors;

    /**
     * @var Decklist|null
     */
    private $precedent;

    /**
     * @var Collection
     */
    private $children;

    /**
     * @var Tournament|null
     */
    private $tournament;

    /**
     * @var Collection
     */
    private $legalities;

    /**
     * @var Modflag|null
     */
    private $modflag;

    /**
     * @var Collection
     */
    private $claims;

    private $isLegal;

    /**
     * @var Mwl|null
     */
    private $mwl;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->isLegal = true;
    }

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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return Decklist
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrettyname()
    {
        return $this->prettyname;
    }

    /**
     * @param string $prettyname
     * @return $this
     */
    public function setPrettyname(string $prettyname)
    {
        $this->prettyname = $prettyname;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     * @return $this
     */
    public function setSummary(string $summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getRawdescription()
    {
        return $this->rawdescription;
    }

    /**
     * @param string $rawdescription
     * @return $this
     */
    public function setRawdescription(string $rawdescription)
    {
        $this->rawdescription = $rawdescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param \DateTime $dateCreation
     * @return $this
     */
    public function setDateCreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     * @return Decklist
     */
    public function setSignature(string $signature)
    {
        $this->signature = $signature;

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
    public function getNbfavorites()
    {
        return $this->nbfavorites;
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
     * @return int
     */
    public function getNbcomments()
    {
        return $this->nbcomments;
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
    public function getDotw()
    {
        return $this->dotw;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
     * @return Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * @param Side $side
     * @return $this
     */
    public function setSide(Side $side)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * @return Card
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param Card $identity
     * @return $this
     */
    public function setIdentity(Card $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return Decklistslot[]|Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
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
    /*
    public function getPrettyName()
    {
        return preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($this->name));
    }
    */

    /**
     * @return Pack
     */
    public function getLastPack()
    {
        return $this->lastPack;
    }

    /**
     * @param Pack $lastPack
     * @return $this
     */
    public function setLastPack(Pack $lastPack)
    {
        $this->lastPack = $lastPack;

        return $this;
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @param Faction $faction
     * @return $this
     */
    public function setFaction(Faction $faction)
    {
        $this->faction = $faction;

        return $this;
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
     * @param User $user
     * @return Decklist
     */
    public function addFavorite(User $user)
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
     * @param User $user
     * @return Decklist
     */
    public function addVote(User $user)
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

    /**
     * Add slots
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
     * @param Decklistslot $slots
     */
    public function removeSlot(Decklistslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * Add comments
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
     * @param Comment $comments
     */
    public function removeComment(Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Remove favorites
     * @param User $favorites
     */
    public function removeFavorite(User $favorites)
    {
        $this->favorites->removeElement($favorites);
    }

    /**
     * Remove votes
     * @param User $votes
     */
    public function removeVote(User $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * @return Deck|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Deck|null $parent
     * @return $this
     */
    public function setParent(Deck $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Add successors
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
     * @param Decklist $successors
     */
    public function removeSuccessor(Decklist $successors)
    {
        $this->successors->removeElement($successors);
    }

    /**
     * @return Collection
     */
    public function getSuccessors()
    {
        return $this->successors;
    }

    /**
     * @return Decklist|null
     */
    public function getPrecedent()
    {
        return $this->precedent;
    }

    /**
     * @param Decklist|null $precedent
     * @return $this
     */
    public function setPrecedent(Decklist $precedent = null)
    {
        $this->precedent = $precedent;

        return $this;
    }

    /**
     * Add children
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
     * @param Deck $children
     */
    public function removeChildren(Deck $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add children
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
     * @param Deck $children
     */
    public function removeChild(Deck $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * @return Tournament|null
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * @param Tournament|null $tournament
     * @return $this
     */
    public function setTournament(Tournament $tournament = null)
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * Add legality
     * @param Legality $legality
     * @return Decklist
     */
    public function addLegality(Legality $legality)
    {
        $this->legalities[] = $legality;

        return $this;
    }

    /**
     * Remove legality
     * @param Legality $legality
     */
    public function removeLegality(Legality $legality)
    {
        $this->legalities->removeElement($legality);
    }

    /**
     * @return Collection
     */
    public function getLegalities()
    {
        return $this->legalities;
    }

    /**
     * @return integer
     */
    public function getModerationStatus()
    {
        return $this->moderationStatus;
    }

    /**
     * @param integer $moderationStatus
     * @return Decklist
     */
    public function setModerationStatus(int $moderationStatus)
    {
        $this->moderationStatus = $moderationStatus;

        return $this;
    }

    /**
     * @return Modflag|null
     */
    public function getModflag()
    {
        return $this->modflag;
    }

    /**
     * @param Modflag $modflag
     * @return $this
     */
    public function setModflag(Modflag $modflag)
    {
        $this->modflag = $modflag;

        return $this;
    }

    /**
     * Add claim
     * @param Claim $claim
     * @return Decklist
     */
    public function addClaim(Claim $claim)
    {
        $this->claims[] = $claim;

        return $this;
    }

    /**
     * Remove claim
     * @param Claim $claim
     */
    public function removeClaim(Claim $claim)
    {
        $this->claims->removeElement($claim);
    }

    /**
     * @return Collection
     */
    public function getClaims()
    {
        return $this->claims;
    }

    public function getIsLegal()
    {
        return $this->isLegal;
    }

    public function setIsLegal(bool $isLegal)
    {
        $this->isLegal = $isLegal;
    }

    /**
     * @return Rotation|null
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    /**
     * @param Rotation|null $rotation
     * @return $this
     */
    public function setRotation(Rotation $rotation = null)
    {
        $this->rotation = $rotation;

        return $this;
    }

    /**
     * @return Mwl|null
     */
    public function getMwl()
    {
        return $this->mwl;
    }

    /**
     * @param Mwl|null $mwl
     * @return $this
     */
    public function setMwl(Mwl $mwl = null)
    {
        $this->mwl = $mwl;

        return $this;
    }
}

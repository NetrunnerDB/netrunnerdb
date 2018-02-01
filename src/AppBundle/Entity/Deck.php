<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Deck
 */
class Deck implements NormalizableInterface, TimestampableInterface
{
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
            'id'            => $this->id,
            'date_creation' => $this->dateCreation->format('c'),
            'date_update'   => $this->dateUpdate->format('c'),
            'name'          => $this->name,
            'description'   => $this->description,
            'mwl_code'      => $this->mwl ? $this->mwl->getCode() : null,
            'cards'         => $cards,
        ];
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $problem;

    /**
     * @var integer
     */
    private $deckSize;

    /**
     * @var integer
     */
    private $influenceSpent;

    /**
     * @var integer
     */
    private $agendaPoints;

    /**
     * @var string
     */
    private $tags;

    private $message;

    /**
     * @var Collection|Deckslot[]
     */
    private $slots;

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
     * @var Pack
     */
    private $lastPack;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->children = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Deck
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Deck
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
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return Deck
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
     * Set problem
     *
     * @param string $problem
     * @return Deck
     */
    public function setProblem($problem = null)
    {
        $this->problem = $problem;

        return $this;
    }

    /**
     * Get problem
     *
     * @return string
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Add slots
     *
     * @param Deckslot $slots
     * @return Deck
     */
    public function addSlot(Deckslot $slots)
    {
        $this->slots[] = $slots;

        return $this;
    }

    /**
     * Remove slots
     *
     * @param Deckslot $slots
     */
    public function removeSlot(Deckslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * @return Deckslot[]|Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Deck
     */
    public function setUser(User $user = null)
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
     * @return Deck
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
     * @return Deck
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Get identity
     *
     * @return Card
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Set lastPack
     *
     * @param Pack $lastPack
     * @return Deck
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
     * Set deckSize
     *
     * @param integer $deckSize
     * @return Deck
     */
    public function setDeckSize($deckSize)
    {
        $this->deckSize = $deckSize;

        return $this;
    }

    /**
     * Get deckSize
     *
     * @return integer
     */
    public function getDeckSize()
    {
        return $this->deckSize;
    }

    /**
     * Set influenceSpent
     *
     * @param integer $influenceSpent
     * @return Deck
     */
    public function setInfluenceSpent($influenceSpent)
    {
        $this->influenceSpent = $influenceSpent;

        return $this;
    }

    /**
     * Get influenceSpent
     *
     * @return integer
     */
    public function getInfluenceSpent()
    {
        return $this->influenceSpent;
    }

    /**
     * Set agendaPoints
     *
     * @param integer $agendaPoints
     * @return Deck
     */
    public function setAgendaPoints($agendaPoints)
    {
        $this->agendaPoints = $agendaPoints;

        return $this;
    }

    /**
     * Get agendaPoints
     *
     * @return integer
     */
    public function getAgendaPoints()
    {
        return $this->agendaPoints;
    }

    /**
     * Set tags
     *
     * @param string $tags
     * @return Deck
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
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

    public function getContent()
    {
        $arr = [];
        foreach ($this->slots as $slot) {
            $arr[$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        ksort($arr);

        return $arr;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @var Collection
     */
    private $children;

    /**
     * @var Decklist
     */
    private $parent;


    /**
     * Add children
     *
     * @param Decklist $children
     * @return Deck
     */
    public function addChildren(Decklist $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Decklist $children
     */
    public function removeChildren(Decklist $children)
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
     * Set parent
     *
     * @param Decklist $parent
     * @return Deck
     */
    public function setParent(Decklist $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Decklist
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @var Collection
     */
    private $changes;


    /**
     * Add children
     *
     * @param Decklist $children
     * @return Deck
     */
    public function addChild(Decklist $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Decklist $children
     */
    public function removeChild(Decklist $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Add changes
     *
     * @param Deckchange $changes
     * @return Deck
     */
    public function addChange(Deckchange $changes)
    {
        $this->changes[] = $changes;

        return $this;
    }

    /**
     * Remove changes
     *
     * @param Deckchange $changes
     */
    public function removeChange(Deckchange $changes)
    {
        $this->changes->removeElement($changes);
    }

    /**
     * Get changes
     *
     * @return Collection
     */
    public function getChanges()
    {
        return $this->changes;
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
     * @return Deck
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

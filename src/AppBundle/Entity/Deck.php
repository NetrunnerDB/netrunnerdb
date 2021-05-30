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

    /**
     * @var string
     */
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
     * @var Collection
     */
    private $children;

    /**
     * @var Decklist|null
     */
    private $parent;

    /**
     * @var Collection
     */
    private $changes;

    /**
     * @var Mwl|null
     */
    private $mwl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "[$this->id] $this->name";
    }

    /**
     * @return array
     */
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
            'tags'          => $this->tags,
        ];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return $this
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

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
     * @return null|string
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * @param string|null $problem
     * @return $this
     */
    public function setProblem(string $problem = null)
    {
        $this->problem = $problem;

        return $this;
    }

    /**
     * @param Deckslot $slots
     * @return $this
     */
    public function addSlot(Deckslot $slots)
    {
        $this->slots[] = $slots;

        return $this;
    }

    /**
     * @param Deckslot $slots
     */
    public function removeSlot(Deckslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * @return Deckslot[]|ArrayCollection|Collection
     */
    public function getSlots()
    {
        return $this->slots;
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
     * @return int
     */
    public function getDeckSize()
    {
        return $this->deckSize;
    }

    /**
     * @param int $deckSize
     * @return $this
     */
    public function setDeckSize(int $deckSize)
    {
        $this->deckSize = $deckSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getInfluenceSpent()
    {
        return $this->influenceSpent;
    }

    /**
     * @param int $influenceSpent
     * @return $this
     */
    public function setInfluenceSpent(int $influenceSpent)
    {
        $this->influenceSpent = $influenceSpent;

        return $this;
    }

    /**
     * @return int
     */
    public function getAgendaPoints()
    {
        return $this->agendaPoints;
    }

    /**
     * @param int $agendaPoints
     * @return $this
     */
    public function setAgendaPoints(int $agendaPoints)
    {
        $this->agendaPoints = $agendaPoints;

        return $this;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     * @return $this
     */
    public function setTags(string $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return array
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
     * @return array
     */
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
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param Decklist $children
     * @return $this
     */
    public function addChildren(Decklist $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * @param Decklist $children
     */
    public function removeChildren(Decklist $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * @return Collection|Decklist[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Decklist|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Decklist|null $parent
     * @return $this
     */
    public function setParent(Decklist $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param Decklist $children
     * @return $this
     */
    public function addChild(Decklist $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * @param Decklist $children
     */
    public function removeChild(Decklist $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * @param Deckchange $changes
     * @return $this
     */
    public function addChange(Deckchange $changes)
    {
        $this->changes[] = $changes;

        return $this;
    }

    /**
     * @param Deckchange $changes
     */
    public function removeChange(Deckchange $changes)
    {
        $this->changes->removeElement($changes);
    }

    /**
     * @return Collection
     */
    public function getChanges()
    {
        return $this->changes;
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

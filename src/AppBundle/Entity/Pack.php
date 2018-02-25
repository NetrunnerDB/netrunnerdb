<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Pack
 */
class Pack implements NormalizableInterface, TimestampableInterface, CodeNameInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime|null
     */
    private $dateRelease;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var integer|null
     */
    private $ffgId;

    /**
     * @var Collection
     */
    private $decklists;

    /**
     * @var Collection
     */
    private $cards;

    /**
     * @var Cycle
     */
    private $cycle;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var int
     */
    private $cardCount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->decklists = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name ?: '(unknown)';
    }

    public function normalize()
    {
        return [
            'code'         => $this->code,
            'cycle_code'   => $this->cycle ? $this->cycle->getCode() : null,
            'date_release' => $this->dateRelease ? $this->dateRelease->format('Y-m-d') : null,
            'name'         => $this->name,
            'position'     => $this->position,
            'size'         => $this->size,
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Pack
     */
    public function setCode(string $code)
    {
        $this->code = $code;

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
     * @return Pack
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateRelease()
    {
        return $this->dateRelease;
    }

    /**
     * @param \DateTime $dateRelease
     * @return Pack
     */
    public function setDateRelease(\DateTime $dateRelease)
    {
        $this->dateRelease = $dateRelease;

        return $this;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param integer $size
     * @return Pack
     */
    public function setSize(int $size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getFfgId()
    {
        return $this->ffgId;
    }

    /**
     * @param integer $ffgId
     * @return Pack
     */
    public function setFfgId(int $ffgId)
    {
        $this->ffgId = $ffgId;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param integer $position
     * @return $this
     */
    public function setPosition(int $position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Add cards
     * @param Card $cards
     * @return Pack
     */
    public function addCard(Card $cards)
    {
        $this->cards[] = $cards;

        return $this;
    }

    /**
     * Remove cards
     * @param Card $cards
     */
    public function removeCard(Card $cards)
    {
        $this->cards->removeElement($cards);
    }

    /**
     * @return Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @return Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * @param Cycle $cycle
     * @return Pack
     */
    public function setCycle(Cycle $cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * Add decklists
     * @param Decklist $decklists
     * @return Pack
     */
    public function addDecklist(Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     * @param Decklist $decklists
     */
    public function removeDecklist(Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
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
     * @return Pack
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
     * @return Pack
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getCardCount(): int
    {
        return $this->cardCount ?? $this->cards->count();
    }

    public function setCardCount(int $cardCount): self
    {
        $this->cardCount = $cardCount;

        return $this;
    }
}

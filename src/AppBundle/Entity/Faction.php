<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Faction
 */
class Faction implements NormalizableInterface, TimestampableInterface, CodeNameInterface
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
     * @var boolean
     */
    private $isMini;

    /**
     * @var Collection
     */
    private $decklists;

    /**
     * @var Side
     */
    private $side;

    /**
     * @var Collection
     */
    private $cards;

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
    private $color;

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
                'code' => $this->code,
                'color' => $this->color,
                'is_mini' => $this->isMini,
                'name' => $this->name,
                'side_code' => $this->side ? $this->side->getCode() : null
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
     * @return Faction
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
     * @return Faction
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMini()
    {
        return $this->isMini;
    }

    /**
     * @param bool $isMini
     * @return $this
     */
    public function setIsMini(bool $isMini)
    {
        $this->isMini = $isMini;

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
     * Add cards
     * @param Card $cards
     * @return Faction
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
     * @return Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * Add decklists
     * @param Decklist $decklists
     * @return Faction
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
     * @return Faction
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
     * @return Faction
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     * @return Faction
     */
    public function setColor(string $color)
    {
        $this->color = $color;

        return $this;
    }
}

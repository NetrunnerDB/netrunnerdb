<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\AbstractTranslatableEntity;
use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Faction
 */
class Faction extends AbstractTranslatableEntity implements NormalizableInterface, TimestampableInterface, CodeNameInterface
{
    public function __toString()
    {
        return $this->name;
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Faction
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set text
     *
     * @param string $name
     * @return Faction
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return bool
     */
    public function getIsMini()
    {
        return $this->isMini;
    }

    /**
     * @var Collection
     */
    private $cards;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->decklists = new ArrayCollection();
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
     * Add cards
     *
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
     *
     * @param Card $cards
     */
    public function removeCard(Card $cards)
    {
        $this->cards->removeElement($cards);
    }

    /**
     * Get cards
     *
     * @return Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Get decklists
     *
     * @return Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }
    
    /**
     * Add decklists
     *
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
     *
     * @param Decklist $decklists
     */
    public function removeDecklist(Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
    }

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;


    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Faction
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
     *
     * @return Faction
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
     * @var string
     */
    private $color;


    /**
     * Set color
     *
     * @param string $color
     *
     * @return Faction
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
}

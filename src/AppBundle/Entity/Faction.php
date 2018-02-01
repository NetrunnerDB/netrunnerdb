<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * Faction
 */
class Faction implements Translatable, \Serializable
{
    public function toString()
    {
        return $this->name;
    }

    public function serialize()
    {
        return [
                'code' => $this->code,
                'color' => $this->color,
                'is_mini' => $this->isMini,
                'name' => $this->name,
                'side_code' => $this->side ? $this->side->getCode() : null
        ];
    }
    
    public function unserialize($serialized)
    {
        throw new \Exception("unserialize() method unsupported");
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decklists;

    /**
     * @var Side
     */
    private $side;

    private $locale = 'en';
    

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
     * Set isMini
     *
     * @param string $isMini
     * @return Faction
     */
    public function setIsMini($isMini)
    {
        $this->isMini = $isMini;

        return $this;
    }

    /**
     * Get isMini
     *
     * @return string
     */
    public function getIsMini()
    {
        return $this->isMini;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
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
     * @return Card
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Get decklists
     *
     * @return \Doctrine\Common\Collections\Collection
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

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
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

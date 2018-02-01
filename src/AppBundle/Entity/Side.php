<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * Side
 */
class Side implements Translatable, \Serializable
{
    public function toString()
    {
        return $this->name;
    }

    public function serialize()
    {
        return [
                'code' => $this->code,
                'name' => $this->name
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
    private $name;

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
     * Set text
     *
     * @param string $name
     * @return Side
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cards;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $factions;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decklists;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->factions = new ArrayCollection();
        $this->decks = new ArrayCollection();
        $this->decklists = new ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param Card $cards
     * @return Side
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
     * Add decks
     *
     * @param Deck $decks
     * @return Side
     */
    public function addDeck(Deck $decks)
    {
        $this->decks[] = $decks;
    
        return $this;
    }

    /**
     * Remove decks
     *
     * @param Deck $decks
     */
    public function removeDeck(Deck $decks)
    {
        $this->decks->removeElement($decks);
    }

    /**
     * Get decks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecks()
    {
        return $this->decks;
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
     * Get decklists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFactions()
    {
        return $this->factions;
    }
    
    /**
     * Add factions
     *
     * @param Faction $factions
     * @return Side
     */
    public function addFaction(Faction $factions)
    {
        $this->factions[] = $factions;

        return $this;
    }

    /**
     * Remove factions
     *
     * @param Faction $factions
     */
    public function removeFaction(Faction $factions)
    {
        $this->factions->removeElement($factions);
    }

    /**
     * Add decklists
     *
     * @param Decklist $decklists
     * @return Side
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
     * @var string
     */
    private $code;


    /**
     * Set code
     *
     * @param string $code
     *
     * @return Side
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
     * @return Side
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
     * @return Side
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
}

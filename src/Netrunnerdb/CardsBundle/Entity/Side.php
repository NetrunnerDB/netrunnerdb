<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * Side
 */
class Side implements Translatable
{
    public function toString() {
		return $this->name;
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
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->factions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decklists = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     * @return Side
     */
    public function addCard(\Netrunnerdb\CardsBundle\Entity\Card $cards)
    {
        $this->cards[] = $cards;
    
        return $this;
    }

    /**
     * Remove cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     */
    public function removeCard(\Netrunnerdb\CardsBundle\Entity\Card $cards)
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
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $decks
     * @return Side
     */
    public function addDeck(\Netrunnerdb\BuilderBundle\Entity\Deck $decks)
    {
        $this->decks[] = $decks;
    
        return $this;
    }

    /**
     * Remove decks
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $decks
     */
    public function removeDeck(\Netrunnerdb\BuilderBundle\Entity\Deck $decks)
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
     * @param \Netrunnerdb\CardsBundle\Entity\Faction $factions
     * @return Side
     */
    public function addFaction(\Netrunnerdb\CardsBundle\Entity\Faction $factions)
    {
        $this->factions[] = $factions;

        return $this;
    }

    /**
     * Remove factions
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Faction $factions
     */
    public function removeFaction(\Netrunnerdb\CardsBundle\Entity\Faction $factions)
    {
        $this->factions->removeElement($factions);
    }

    /**
     * Add decklists
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklists
     * @return Side
     */
    public function addDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklists
     */
    public function removeDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklists)
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
}

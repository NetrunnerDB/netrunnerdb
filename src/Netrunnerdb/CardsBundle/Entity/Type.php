<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * Type
 */
class Type implements Translatable
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
     * @return Type
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
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     * @return Type
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
     * @var string
     */
    private $nameIt;


    /**
     * Set nameIt
     *
     * @param string $nameIt
     * @return Type
     */
    public function setNameIt($nameIt)
    {
        $this->nameIt = $nameIt;

        return $this;
    }

    /**
     * Get nameIt
     *
     * @return string 
     */
    public function getNameIt()
    {
        return $this->nameIt;
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
     * @return Type
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

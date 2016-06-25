<?php

namespace Netrunnerdb\BuilderBundle\Entity;

/**
 * Deckslot
 */
class Deckslot
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var Netrunnerdb\BuilderBundle\Entity\Deck
     */
    private $deck;

    /**
     * @var Netrunnerdb\CardsBundle\Entity\Card
     */
    private $card;
    
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
     * Set quantity
     *
     * @param integer $quantity
     * @return Deckcontent
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    
        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set deck
     *
     * @param string $deck
     * @return Deck
     */
    public function setDeck($deck)
    {
    	$this->deck = $deck;
    
    	return $this;
    }
    
    /**
     * Get deck
     *
     * @return string
     */
    public function getDeck()
    {
    	return $this->deck;
    }

    /**
     * Set card
     *
     * @param string $card
     * @return Card
     */
    public function setCard($card)
    {
    	$this->card = $card;
    
    	return $this;
    }
    
    /**
     * Get card
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Card
     */
    public function getCard()
    {
    	return $this->card;
    }
    
}
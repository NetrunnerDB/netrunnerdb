<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\SlotInterface;

/**
 * Deckslot
 */
class Deckslot implements SlotInterface
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
     * @var \AppBundle\Entity\Deck
     */
    private $deck;

    /**
     * @var \AppBundle\Entity\Card
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
     * @return Deckslot
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
     * @return Deckslot
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
     * @return Deckslot
     */
    public function setCard($card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return \AppBundle\Entity\Card
     */
    public function getCard()
    {
        return $this->card;
    }
}
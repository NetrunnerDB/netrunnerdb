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
     * @var Deck
     */
    private $deck;

    /**
     * @var Card
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
     * @param Deck $deck
     * @return $this
     */
    public function setDeck(Deck $deck)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param Card $card
     * @return $this
     */
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }
}

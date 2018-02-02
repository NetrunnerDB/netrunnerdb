<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\SlotInterface;

/**
 * Decklistslot
 */
class Decklistslot implements SlotInterface
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
     * @var Decklist
     */
    private $decklist;

    /**
     * @var Card
     */
    private $card;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param integer $quantity
     * @return Decklistslot
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * @param Decklist $decklist
     * @return $this
     */
    public function setDecklist(Decklist $decklist)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
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
}

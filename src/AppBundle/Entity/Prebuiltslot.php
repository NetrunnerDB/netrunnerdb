<?php

namespace AppBundle\Entity;

/**
 * Prebuiltslot
 */
class Prebuiltslot
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
     * @var Prebuilt
     */
    private $prebuilt;

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
     *
     * @return Prebuiltslot
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
     * Set prebuilt
     *
     * @param Prebuilt $prebuilt
     *
     * @return Prebuiltslot
     */
    public function setPrebuilt(Prebuilt $prebuilt)
    {
        $this->prebuilt = $prebuilt;

        return $this;
    }

    /**
     * Get prebuilt
     *
     * @return Prebuilt
     */
    public function getPrebuilt()
    {
        return $this->prebuilt;
    }

    /**
     * Set card
     *
     * @param Card $card
     *
     * @return Prebuiltslot
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

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
     * @return Prebuiltslot
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Prebuilt
     */
    public function getPrebuilt()
    {
        return $this->prebuilt;
    }

    /**
     * @param Prebuilt $prebuilt
     * @return Prebuiltslot
     */
    public function setPrebuilt(Prebuilt $prebuilt)
    {
        $this->prebuilt = $prebuilt;

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
     * @return Prebuiltslot
     */
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }
}

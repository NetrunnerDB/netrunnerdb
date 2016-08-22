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
     * @var \AppBundle\Entity\Prebuilt
     */
    private $prebuilt;

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
     * @param \AppBundle\Entity\Prebuilt $prebuilt
     *
     * @return Prebuiltslot
     */
    public function setPrebuilt(\AppBundle\Entity\Prebuilt $prebuilt = null)
    {
        $this->prebuilt = $prebuilt;

        return $this;
    }

    /**
     * Get prebuilt
     *
     * @return \AppBundle\Entity\Prebuilt
     */
    public function getPrebuilt()
    {
        return $this->prebuilt;
    }

    /**
     * Set card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return Prebuiltslot
     */
    public function setCard(\AppBundle\Entity\Card $card = null)
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

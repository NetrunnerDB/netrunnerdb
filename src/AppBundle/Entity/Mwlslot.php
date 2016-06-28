<?php

namespace AppBundle\Entity;

/**
 * Mwlcard
 */
class Mwlslot
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $penalty;


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
     * Set penalty
     *
     * @param integer $penalty
     *
     * @return Mwlcard
     */
    public function setPenalty($penalty)
    {
        $this->penalty = $penalty;

        return $this;
    }

    /**
     * Get penalty
     *
     * @return integer
     */
    public function getPenalty()
    {
        return $this->penalty;
    }
    /**
     * @var \AppBundle\Entity\Mwl
     */
    private $mwl;

    /**
     * @var \AppBundle\Entity\Card
     */
    private $card;


    /**
     * Set mwl
     *
     * @param \AppBundle\Entity\Mwl $mwl
     *
     * @return Mwlslot
     */
    public function setMwl(\AppBundle\Entity\Mwl $mwl = null)
    {
        $this->mwl = $mwl;

        return $this;
    }

    /**
     * Get mwl
     *
     * @return \AppBundle\Entity\Mwl
     */
    public function getMwl()
    {
        return $this->mwl;
    }

    /**
     * Set card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return Mwlslot
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

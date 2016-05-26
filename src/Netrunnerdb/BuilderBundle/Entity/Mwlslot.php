<?php

namespace Netrunnerdb\BuilderBundle\Entity;

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
     * @var \Netrunnerdb\BuilderBundle\Entity\Mwl
     */
    private $mwl;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Card
     */
    private $card;


    /**
     * Set mwl
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Mwl $mwl
     *
     * @return Mwlslot
     */
    public function setMwl(\Netrunnerdb\BuilderBundle\Entity\Mwl $mwl = null)
    {
        $this->mwl = $mwl;

        return $this;
    }

    /**
     * Get mwl
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Mwl
     */
    public function getMwl()
    {
        return $this->mwl;
    }

    /**
     * Set card
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $card
     *
     * @return Mwlslot
     */
    public function setCard(\Netrunnerdb\CardsBundle\Entity\Card $card = null)
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

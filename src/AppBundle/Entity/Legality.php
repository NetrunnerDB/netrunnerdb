<?php

namespace AppBundle\Entity;

/**
 * Legality
 */
class Legality
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isLegal;

    /**
     * @var \AppBundle\Entity\Decklist
     */
    private $decklist;

    /**
     * @var \AppBundle\Entity\Mwl
     */
    private $mwl;


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
     * Set isLegal
     *
     * @param boolean $isLegal
     *
     * @return Legality
     */
    public function setIsLegal($isLegal)
    {
        $this->isLegal = $isLegal;

        return $this;
    }

    /**
     * Get isLegal
     *
     * @return boolean
     */
    public function getIsLegal()
    {
        return $this->isLegal;
    }

    /**
     * Set decklist
     *
     * @param \AppBundle\Entity\Decklist $decklist
     *
     * @return Legality
     */
    public function setDecklist(\AppBundle\Entity\Decklist $decklist = null)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return \AppBundle\Entity\Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * Set mwl
     *
     * @param \AppBundle\Entity\Mwl $mwl
     *
     * @return Legality
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
}

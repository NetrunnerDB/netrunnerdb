<?php

namespace Netrunnerdb\BuilderBundle\Entity;

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
     * @var \Netrunnerdb\BuilderBundle\Entity\Decklist
     */
    private $decklist;

    /**
     * @var \Netrunnerdb\BuilderBundle\Entity\Mwl
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
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklist
     *
     * @return Legality
     */
    public function setDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklist = null)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * Set mwl
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Mwl $mwl
     *
     * @return Legality
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
}

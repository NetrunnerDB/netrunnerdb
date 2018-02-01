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
     * @var Decklist
     */
    private $decklist;

    /**
     * @var Mwl
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
     * @param Decklist $decklist
     *
     * @return Legality
     */
    public function setDecklist(Decklist $decklist = null)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * Set mwl
     *
     * @param Mwl $mwl
     *
     * @return Legality
     */
    public function setMwl(Mwl $mwl = null)
    {
        $this->mwl = $mwl;

        return $this;
    }

    /**
     * Get mwl
     *
     * @return Mwl
     */
    public function getMwl()
    {
        return $this->mwl;
    }
}

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
     * @param Decklist $decklist
     * @return $this
     */
    public function setDecklist(Decklist $decklist)
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
     * @param Mwl $mwl
     * @return $this
     */
    public function setMwl(Mwl $mwl)
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

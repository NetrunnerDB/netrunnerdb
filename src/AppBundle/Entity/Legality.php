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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getIsLegal()
    {
        return $this->isLegal;
    }

    /**
     * @param boolean $isLegal
     * @return Legality
     */
    public function setIsLegal(bool $isLegal)
    {
        $this->isLegal = $isLegal;

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
     * @return Mwl
     */
    public function getMwl()
    {
        return $this->mwl;
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
}

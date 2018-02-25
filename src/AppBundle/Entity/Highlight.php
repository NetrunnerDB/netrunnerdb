<?php

namespace AppBundle\Entity;

/**
 * Highlight
 */
class Highlight
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Decklist
     */
    private $decklist;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
}

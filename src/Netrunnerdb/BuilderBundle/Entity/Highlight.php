<?php

namespace Netrunnerdb\BuilderBundle\Entity;

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
     * @var string
     */
    private $decklist;


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
     * Set decklist
     *
     * @param string $decklist
     * @return Highlight
     */
    public function setDecklist($decklist)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return string 
     */
    public function getDecklist()
    {
        return $this->decklist;
    }
}

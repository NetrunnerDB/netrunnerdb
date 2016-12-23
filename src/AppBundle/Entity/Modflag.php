<?php

namespace AppBundle\Entity;

/**
 * Modflag
 */
class Modflag
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     *
     * @return Modflag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decklists;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->decklists = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add decklist
     *
     * @param \AppBundle\Entity\Decklist $decklist
     *
     * @return Modflag
     */
    public function addDecklist(\AppBundle\Entity\Decklist $decklist)
    {
        $this->decklists[] = $decklist;

        return $this;
    }

    /**
     * Remove decklist
     *
     * @param \AppBundle\Entity\Decklist $decklist
     */
    public function removeDecklist(\AppBundle\Entity\Decklist $decklist)
    {
        $this->decklists->removeElement($decklist);
    }

    /**
     * Get decklists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }
}

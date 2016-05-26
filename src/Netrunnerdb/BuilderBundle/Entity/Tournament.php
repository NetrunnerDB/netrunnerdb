<?php

namespace Netrunnerdb\BuilderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tournament
 */
class Tournament
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $description;


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
     * Set description
     *
     * @param string $description
     * @return Tournament
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
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
     * Add decklists
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklists
     * @return Tournament
     */
    public function addDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklists
     */
    public function removeDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
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

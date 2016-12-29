<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Exclude;

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
    private $reason;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @Exclude
     */
    private $decklists;

    
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
     * Set reason
     *
     * @param string $reason
     *
     * @return Modflag
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
    
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

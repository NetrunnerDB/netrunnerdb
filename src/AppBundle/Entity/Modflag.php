<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var Collection
     * @Exclude
     */
    private $decklists;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->decklists = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return Modflag
     */
    public function setReason(string $reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Add decklist
     * @param Decklist $decklist
     * @return Modflag
     */
    public function addDecklist(Decklist $decklist)
    {
        $this->decklists[] = $decklist;

        return $this;
    }

    /**
     * Remove decklist
     * @param Decklist $decklist
     */
    public function removeDecklist(Decklist $decklist)
    {
        $this->decklists->removeElement($decklist);
    }

    /**
     * @return Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }
}

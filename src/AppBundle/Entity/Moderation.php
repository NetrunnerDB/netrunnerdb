<?php

namespace AppBundle\Entity;

/**
 * Moderation
 */
class Moderation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateCreation;

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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Moderation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
    /**
     * @var \AppBundle\Entity\Decklist
     */
    private $decklist;

    /**
     * @var \AppBundle\Entity\User
     */
    private $moderator;


    /**
     * Set decklist
     *
     * @param \AppBundle\Entity\Decklist $decklist
     *
     * @return Moderation
     */
    public function setDecklist(\AppBundle\Entity\Decklist $decklist = null)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return \AppBundle\Entity\Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * Set moderator
     *
     * @param \AppBundle\Entity\User $moderator
     *
     * @return Moderation
     */
    public function setModerator(\AppBundle\Entity\User $moderator = null)
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * Get moderator
     *
     * @return \AppBundle\Entity\User
     */
    public function getModerator()
    {
        return $this->moderator;
    }
    /**
     * @var integer
     */
    private $statusBefore;

    /**
     * @var integer
     */
    private $statusAfter;


    /**
     * Set statusBefore
     *
     * @param integer $statusBefore
     *
     * @return Moderation
     */
    public function setStatusBefore($statusBefore)
    {
        $this->statusBefore = $statusBefore;

        return $this;
    }

    /**
     * Get statusBefore
     *
     * @return integer
     */
    public function getStatusBefore()
    {
        return $this->statusBefore;
    }

    /**
     * Set statusAfter
     *
     * @param integer $statusAfter
     *
     * @return Moderation
     */
    public function setStatusAfter($statusAfter)
    {
        $this->statusAfter = $statusAfter;

        return $this;
    }

    /**
     * Get statusAfter
     *
     * @return integer
     */
    public function getStatusAfter()
    {
        return $this->statusAfter;
    }
}

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
     * @var Decklist
     */
    private $decklist;

    /**
     * @var User
     */
    private $moderator;

    /**
     * @var integer
     */
    private $statusBefore;

    /**
     * @var integer
     */
    private $statusAfter;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param \DateTime $dateCreation
     * @return Moderation
     */
    public function setDateCreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;

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
     * @return Moderation
     */
    public function setDecklist(Decklist $decklist)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * @return User
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * @param User $moderator
     * @return Moderation
     */
    public function setModerator(User $moderator)
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatusBefore()
    {
        return $this->statusBefore;
    }

    /**
     * @param integer $statusBefore
     * @return Moderation
     */
    public function setStatusBefore(int $statusBefore)
    {
        $this->statusBefore = $statusBefore;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatusAfter()
    {
        return $this->statusAfter;
    }

    /**
     * @param integer $statusAfter
     * @return Moderation
     */
    public function setStatusAfter(int $statusAfter)
    {
        $this->statusAfter = $statusAfter;

        return $this;
    }
}

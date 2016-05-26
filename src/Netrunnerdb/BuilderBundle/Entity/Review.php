<?php

namespace Netrunnerdb\BuilderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Netrunnerdb\UserBundle\Entity\User;

/**
 * Review
 */
class Review
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
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var string
     */
    private $rawtext;

    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $nbvotes;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Card
     */
    private $card;

    /**
     * @var \Netrunnerdb\UserBundle\Entity\User
     */
    private $user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $votes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return Review
     */
    public function setDatecreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDatecreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return Review
     */
    public function setDateupdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateupdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set rawtext
     *
     * @param string $rawtext
     * @return Review
     */
    public function setRawtext($rawtext)
    {
        $this->rawtext = $rawtext;

        return $this;
    }

    /**
     * Get rawtext
     *
     * @return string
     */
    public function getRawtext()
    {
        return $this->rawtext;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Review
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set nbvotes
     *
     * @param integer $nbvotes
     * @return Review
     */
    public function setNbvotes($nbvotes)
    {
        $this->nbvotes = $nbvotes;

        return $this;
    }

    /**
     * Get nbvotes
     *
     * @return integer
     */
    public function getNbvotes()
    {
        return $this->nbvotes;
    }

    /**
     * Set card
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $card
     * @return Review
     */
    public function setCard(\Netrunnerdb\CardsBundle\Entity\Card $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set user
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $user
     * @return Review
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Netrunnerdb\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add votes
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $votes
     * @return Review
     */
    public function addVote(\Netrunnerdb\UserBundle\Entity\User $user)
    {
        $this->votes[] = $user;

        return $this;
    }

    /**
     * Remove votes
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $votes
     */
    public function removeVote(\Netrunnerdb\UserBundle\Entity\User $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $comments;


    /**
     * Add comments
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Reviewcomment $comments
     * @return Review
     */
    public function addComment(\Netrunnerdb\BuilderBundle\Entity\Reviewcomment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Reviewcomment $comments
     */
    public function removeComment(\Netrunnerdb\BuilderBundle\Entity\Reviewcomment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
}

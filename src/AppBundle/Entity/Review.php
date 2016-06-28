<?php

namespace AppBundle\Entity;

use AppBundle\Entity\User;

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
     * @var \AppBundle\Entity\Card
     */
    private $card;

    /**
     * @var \AppBundle\Entity\User
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
     * @param \AppBundle\Entity\Card $card
     * @return Review
     */
    public function setCard(\AppBundle\Entity\Card $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return \AppBundle\Entity\Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
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
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add votes
     *
     * @param \AppBundle\Entity\User $votes
     * @return Review
     */
    public function addVote(\AppBundle\Entity\User $user)
    {
        $this->votes[] = $user;

        return $this;
    }

    /**
     * Remove votes
     *
     * @param \AppBundle\Entity\User $votes
     */
    public function removeVote(\AppBundle\Entity\User $votes)
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
     * @param \AppBundle\Entity\Reviewcomment $comments
     * @return Review
     */
    public function addComment(\AppBundle\Entity\Reviewcomment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \AppBundle\Entity\Reviewcomment $comments
     */
    public function removeComment(\AppBundle\Entity\Reviewcomment $comments)
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

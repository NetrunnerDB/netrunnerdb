<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
     * @var Card
     */
    private $card;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Collection
     */
    private $votes;

    /**
     * @var Collection
     */
    private $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

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
     * @return Review
     */
    public function setDatecreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateupdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return Review
     */
    public function setDateupdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getRawtext()
    {
        return $this->rawtext;
    }

    /**
     * @param string $rawtext
     * @return Review
     */
    public function setRawtext(string $rawtext)
    {
        $this->rawtext = $rawtext;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Review
     */
    public function setText(string $text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return integer
     */
    public function getNbvotes()
    {
        return $this->nbvotes;
    }

    /**
     * @param integer $nbvotes
     * @return Review
     */
    public function setNbvotes(int $nbvotes)
    {
        $this->nbvotes = $nbvotes;

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card $card
     * @return Review
     */
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Review
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addVote(User $user)
    {
        $this->votes[] = $user;

        return $this;
    }

    /**
     * Remove votes
     * @param User $votes
     */
    public function removeVote(User $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * @return Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Add comments
     * @param Reviewcomment $comments
     * @return Review
     */
    public function addComment(Reviewcomment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     * @param Reviewcomment $comments
     */
    public function removeComment(Reviewcomment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * @return Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}

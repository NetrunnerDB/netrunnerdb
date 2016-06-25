<?php

namespace Netrunnerdb\BuilderBundle\Entity;

/**
 * Reviewcomment
 */
class Reviewcomment
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
    private $text;

    /**
     * @var \Netrunnerdb\UserBundle\Entity\User
     */
    private $author;

    /**
     * @var \Netrunnerdb\BuilderBundle\Entity\Review
     */
    private $review;


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
     * @return Reviewcomment
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
     * @return Reviewcomment
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
     * Set text
     *
     * @param string $text
     * @return Reviewcomment
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
     * Set author
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $author
     * @return Reviewcomment
     */
    public function setAuthor(\Netrunnerdb\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Netrunnerdb\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set review
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Review $review
     * @return Reviewcomment
     */
    public function setReview(\Netrunnerdb\BuilderBundle\Entity\Review $review = null)
    {
        $this->review = $review;

        return $this;
    }

    /**
     * Get review
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Review
     */
    public function getReview()
    {
        return $this->review;
    }
}

<?php

namespace AppBundle\Entity;

/**
 * Comment
 */
class Comment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var boolean
     */
    private $hidden;
    
    /**
     * @var User
     */
    private $author;

    /**
     * @var Decklist
     */
    private $decklist;

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
     * Set text
     *
     * @param string $text
     * @return Comment
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Comment
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
     * Set hidden
     *
     * @param boolean $hidden
     * @return Comment
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    
        return $this;
    }
    
    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param User $author
     * @return $this
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param Decklist $decklist
     * @return $this
     */
    public function setDecklist(Decklist $decklist)
    {
        $this->decklist = $decklist;
    
        return $this;
    }

    /**
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }
}

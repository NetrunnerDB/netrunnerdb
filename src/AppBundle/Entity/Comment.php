<?php

namespace AppBundle\Entity;

use AppBundle\Entity\User;
use AppBundle\Entity\Decklist;

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
     * Set author
     *
     * @param string $author
     * @return User
     */
    public function setAuthor($author)
    {
    	$this->author = $author;
    
    	return $this;
    }
    
    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
    	return $this->author;
    }

    /**
     * Set decklist
     *
     * @param string $decklist
     * @return Decklist
     */
    public function setDecklist($decklist)
    {
    	$this->decklist = $decklist;
    
    	return $this;
    }
    
    /**
     * Get decklist
     *
     * @return string
     */
    public function getDecklist()
    {
    	return $this->decklist;
    }
}

<?php

namespace AppBundle\Entity;

/**
 * Pack
 */
class Pack implements \Gedmo\Translatable\Translatable, \Serializable
{
    public function toString() {
		return $this->name;
	}

	public function serialize() {
		return [
				'code' => $this->code,
				'cycle_code' => $this->cycle ? $this->cycle->getCode() : null,
				'date_release' => $this->dateRelease ? $this->dateRelease->format('Y-m-d') : null, 
				'name' => $this->name,
				'position' => $this->position,
				'size' => $this->size
		];
	}
	
	public function unserialize($serialized) {
		throw new \Exception("unserialize() method unsupported");
	}
	
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $dateRelease;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var integer
     */
    private $ffgId;

    /**
     * @var string 
     */
    private $locale = 'en';
    
    /**
     * @var \Doctrine\Common\Collections\Collection
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
     * Set code
     *
     * @param string $code
     * @return Pack
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Pack
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
    	return $this->name;
    }

    /**
     * Set dateRelease
     *
     * @param \DateTime $dateRelease
     * @return Pack
     */
    public function setDateRelease($dateRelease)
    {
        $this->dateRelease = $dateRelease;
    
        return $this;
    }

    /**
     * Get dateRelease
     *
     * @return \DateTime 
     */
    public function getDateRelease()
    {
        return $this->dateRelease;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Pack
     */
    public function setSize($size)
    {
        $this->size = $size;
    
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set ffgId
     *
     * @param integer $ffgId
     * @return Pack
     */
    public function setFfgId($ffgId)
    {
        $this->ffgId = $ffgId;

        return $this;
    }

    /**
     * Get ffgId
     *
     * @return integer
     */
    public function getFfgId()
    {
        return $this->ffgId;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Card
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cards;

    /**
     * @var \AppBundle\Entity\Cycle
     */
    private $cycle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ts = new \DateTime(); 
    	$this->cards = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->decklists = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param \AppBundle\Entity\Card $cards
     * @return Pack
     */
    public function addCard(\AppBundle\Entity\Card $cards)
    {
        $this->cards[] = $cards;
    
        return $this;
    }

    /**
     * Remove cards
     *
     * @param \AppBundle\Entity\Card $cards
     */
    public function removeCard(\AppBundle\Entity\Card $cards)
    {
        $this->cards->removeElement($cards);
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set cycle
     *
     * @param \AppBundle\Entity\Cycle $cycle
     * @return Pack
     */
    public function setCycle(\AppBundle\Entity\Cycle $cycle = null)
    {
        $this->cycle = $cycle;
    
        return $this;
    }

    /**
     * Get cycle
     *
     * @return \AppBundle\Entity\Cycle 
     */
    public function getCycle()
    {
        return $this->cycle;
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

    /**
     * Add decklists
     *
     * @param \AppBundle\Entity\Decklist $decklists
     * @return Pack
     */
    public function addDecklist(\AppBundle\Entity\Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     *
     * @param \AppBundle\Entity\Decklist $decklists
     */
    public function removeDecklist(\AppBundle\Entity\Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
    }

    public function setTranslatableLocale($locale)
    {
    	$this->locale = $locale;
    }
    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;


    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Pack
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
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Pack
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }
    
    
}

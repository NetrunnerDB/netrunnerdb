<?php

namespace AppBundle\Entity;

/**
 * Cycle
 */
class Cycle implements \Gedmo\Translatable\Translatable, \Serializable
{
    public function toString() {
		return $this->name;
	}
	
	public function serialize() {
		return [
				'code' => $this->code,
				'name' => $this->name,
				'position' => $this->position,
				'size' => $this->size,
                'rotated' => $this->rotated
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
     * @var integer
     */
    private $position;

    /**
     * @var integer
     */
    private $size;
    
    /**
     * @var boolean
     */
    private $rotated;

    /**
     * @var string
     */
    private $locale = 'en';
    

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
     * @return Cycle
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
     * @return Cycle
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
     * Set position
     *
     * @param integer $position
     * @return Cycle
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
     * Set size
     *
     * @param integer $size
     * @return Cycle
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
     * Set rotated
     *
     * @param boolean $rotated
     * @return Cycle
     */
    public function setRotated($rotated)
    {
        $this->rotated = $rotated;
   
        return $this;
    }
   
    /**
     * Get rotated
     *
     * @return boolean
     */
    public function getRotated()
    {
        return $this->rotated;
    }


    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $packs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->packs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add packs
     *
     * @param \AppBundle\Entity\Pack $packs
     * @return Cycle
     */
    public function addPack(\AppBundle\Entity\Pack $packs)
    {
        $this->packs[] = $packs;
    
        return $this;
    }

    /**
     * Remove packs
     *
     * @param \AppBundle\Entity\Pack $packs
     */
    public function removePack(\AppBundle\Entity\Pack $packs)
    {
        $this->packs->removeElement($packs);
    }

    /**
     * Get packs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPacks()
    {
        return $this->packs;
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
     * @return Cycle
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
     * @return Cycle
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

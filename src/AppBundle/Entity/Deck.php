<?php

namespace AppBundle\Entity;

/**
 * Deck
 */
class Deck implements \Serializable
{
	function __toString() {
		return "[$this->id] $this->name";
	}
	
	function serialize() {
		$cards = [];
		foreach($this->slots as $slot) {
			$cards[$slot->getCard()->getCode()] = $slot->getQuantity();
		}
	
		return  [
				'id' => $this->id,
				'date_creation' => $this->dateCreation->format('c'),
				'date_update' => $this->dateUpdate->format('c'),
				'name' => $this->name,
				'description' => $this->description,
				'mwl_code' => $this->mwl ? $this->mwl->getCode() : null,
				'cards' => $cards
		];
	}
	
	function unserialize($serialized) {
		throw new \Exception("unserialize() method unsupported");
	}
	
    /**
     * @var integer
     */
    private $id;
	
    /**
     * @var string
     */
    private $name;

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
    private $description;
    
    /**
     * @var string
     */
    private $problem;
    
    /**
     * @var integer
     */
    private $deckSize;

    /**
     * @var integer
     */
    private $influenceSpent;

    /**
     * @var integer
     */
    private $agendaPoints;

    /**
     * @var string
     */
    private $tags;
    
    private $message;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * @var \AppBundle\Entity\User
     */
    private $user;

    /**
     * @var \AppBundle\Entity\Side
     */
    private $side;

    /**
     * @var AppBundle\Entity\Card
     */
    private $identity;
    
    /**
     * @var AppBundle\Entity\Pack
     */
    private $lastPack;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descendants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Deck
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Deck
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
     * @return Deck
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
     * Set description
     *
     * @param string $description
     * @return List
     */
    public function setDescription($description)
    {
    	$this->description = $description;
    
    	return $this;
    }
    
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
    	return $this->description;
    }
    
    /**
     * Set problem
     *
     * @param string $problem
     * @return Deck
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;
    
        return $this;
    }

    /**
     * Get problem
     *
     * @return string
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Add slots
     *
     * @param \AppBundle\Entity\Deckslot $slots
     * @return Deck
     */
    public function addSlot(\AppBundle\Entity\Deckslot $slots)
    {
        $this->slots[] = $slots;
    
        return $this;
    }

    /**
     * Remove slots
     *
     * @param \AppBundle\Entity\Deckslot $slots
     */
    public function removeSlot(\AppBundle\Entity\Deckslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * Get slots
     *
     * @return \AppBundle\Entity\Deckslot[]
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Deck
     */
    public function setUser(\AppBundle\Entity\User $user = null)
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
     * Set side
     *
     * @param \AppBundle\Entity\Side $side
     * @return Deck
     */
    public function setSide(\AppBundle\Entity\Side $side = null)
    {
        $this->side = $side;
    
        return $this;
    }

    /**
     * Get side
     *
     * @return \AppBundle\Entity\Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set identity
     *
     * @param \AppBundle\Entity\Card $identity
     * @return Deck
     */
    public function setIdentity($identity)
    {
    	$this->identity = $identity;
    
    	return $this;
    }
    
    /**
     * Get identity
     *
     * @return \AppBundle\Entity\Card
     */
    public function getIdentity()
    {
    	return $this->identity;
    }

    /**
     * Set lastPack
     *
     * @param \AppBundle\Entity\Pack $lastPack
     * @return Deck
     */
    public function setLastPack($lastPack)
    {
    	$this->lastPack = $lastPack;
    
    	return $this;
    }
    
    /**
     * Get lastPack
     *
     * @return \AppBundle\Entity\Pack
     */
    public function getLastPack()
    {
    	return $this->lastPack;
    }
    
    /**
     * Set deckSize
     *
     * @param integer $deckSize
     * @return Deck
     */
    public function setDeckSize($deckSize)
    {
        $this->deckSize = $deckSize;
    
        return $this;
    }

    /**
     * Get deckSize
     *
     * @return integer
     */
    public function getDeckSize()
    {
        return $this->deckSize;
    }

    /**
     * Set influenceSpent
     *
     * @param integer $influenceSpent
     * @return Deck
     */
    public function setInfluenceSpent($influenceSpent)
    {
        $this->influenceSpent = $influenceSpent;
    
        return $this;
    }

    /**
     * Get influenceSpent
     *
     * @return integer
     */
    public function getInfluenceSpent()
    {
        return $this->influenceSpent;
    }

    /**
     * Set agendaPoints
     *
     * @param integer $agendaPoints
     * @return Deck
     */
    public function setAgendaPoints($agendaPoints)
    {
        $this->agendaPoints = $agendaPoints;
    
        return $this;
    }

    /**
     * Get agendaPoints
     *
     * @return integer
     */
    public function getAgendaPoints()
    {
        return $this->agendaPoints;
    }

    /**
     * Set tags
     *
     * @param string $tags
     * @return Deck
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    
        return $this;
    }
    
    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    /**
     * Get cards
     *
     * @return Cards[]
     */
    public function getCards()
    {
    	$arr = array();
    	foreach($this->slots as $slot) {
    		$card = $slot->getCard();
    		$arr[$card->getCode()] = array('qty' => $slot->getQuantity(), 'card' => $card);
    	}
    	return $arr;
    }

    public function getContent()
    {
    	$arr = array();
    	foreach($this->slots as $slot) {
    		$arr[$slot->getCard()->getCode()] = $slot->getQuantity();
    	}
    	ksort($arr);
    	return $arr;
    }
    
    public function getMessage()
    {
    	return $this->message;
    }
    
    public function setMessage($message)
    {
    	$this->message = $message;
    	return $this;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \AppBundle\Entity\Decklist
     */
    private $parent;


    /**
     * Add children
     *
     * @param \AppBundle\Entity\Decklist $children
     * @return Deck
     */
    public function addChildren(\AppBundle\Entity\Decklist $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \AppBundle\Entity\Decklist $children
     */
    public function removeChildren(\AppBundle\Entity\Decklist $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Decklist $parent
     * @return Deck
     */
    public function setParent(\AppBundle\Entity\Decklist $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Decklist
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $changes;


    /**
     * Add children
     *
     * @param \AppBundle\Entity\Decklist $children
     * @return Deck
     */
    public function addChild(\AppBundle\Entity\Decklist $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \AppBundle\Entity\Decklist $children
     */
    public function removeChild(\AppBundle\Entity\Decklist $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Add changes
     *
     * @param \AppBundle\Entity\Deckchange $changes
     * @return Deck
     */
    public function addChange(\AppBundle\Entity\Deckchange $changes)
    {
        $this->changes[] = $changes;

        return $this;
    }

    /**
     * Remove changes
     *
     * @param \AppBundle\Entity\Deckchange $changes
     */
    public function removeChange(\AppBundle\Entity\Deckchange $changes)
    {
        $this->changes->removeElement($changes);
    }

    /**
     * Get changes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChanges()
    {
        return $this->changes;
    }
    /**
     * @var \AppBundle\Entity\Mwl
     */
    private $mwl;


    /**
     * Set mwl
     *
     * @param \AppBundle\Entity\Mwl $mwl
     *
     * @return Deck
     */
    public function setMwl(\AppBundle\Entity\Mwl $mwl = null)
    {
        $this->mwl = $mwl;

        return $this;
    }

    /**
     * Get mwl
     *
     * @return \AppBundle\Entity\Mwl
     */
    public function getMwl()
    {
        return $this->mwl;
    }
}

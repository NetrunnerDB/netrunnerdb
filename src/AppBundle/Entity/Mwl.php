<?php

namespace AppBundle\Entity;

/**
 * Mwl
 */
class Mwl implements \Serializable
{
	public function toString() {
		return $this->name;
	}
	
	public function serialize() {
		$cards = [];
		foreach($this->slots as $slot) {
			$cards[$slot->getCard()->getCode()] = $slot->getPenalty();
		}
	
		return  [
				'id' => $this->id,
				'date_creation' => $this->dateCreation ? $this->dateCreation->format('c') : null,
				'date_update' => $this->dateUpdate ? $this->dateUpdate->format('c') : null,
				'code' => $this->code,
				'name' => $this->name,
				'active' => $this->active,
				'date_start' => $this->dateStart ? $this->dateStart->format('Y-m-d') : null,
				'cards' => $cards
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
    private $dateStart;

    /**
     * @var boolean
     */
    private $active;


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
     *
     * @return Mwl
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
     *
     * @return Mwl
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
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return Mwl
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Mwl
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * Constructor
     */
    public function __construct()
    {
    	$this->active = false;
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add slot
     *
     * @param \AppBundle\Entity\Mwlslot $slot
     *
     * @return Mwl
     */
    public function addSlot(\AppBundle\Entity\Mwlslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param \AppBundle\Entity\Mwlslot $slot
     */
    public function removeSlot(\AppBundle\Entity\Mwlslot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Get slots
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decks;


    /**
     * Add deck
     *
     * @param \AppBundle\Entity\Deck $deck
     *
     * @return Mwl
     */
    public function addDeck(\AppBundle\Entity\Deck $deck)
    {
        $this->decks[] = $deck;

        return $this;
    }

    /**
     * Remove deck
     *
     * @param \AppBundle\Entity\Deck $deck
     */
    public function removeDeck(\AppBundle\Entity\Deck $deck)
    {
        $this->decks->removeElement($deck);
    }

    /**
     * Get decks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecks()
    {
        return $this->decks;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $legalities;


    /**
     * Add legality
     *
     * @param \AppBundle\Entity\Legality $legality
     *
     * @return Mwl
     */
    public function addLegality(\AppBundle\Entity\Legality $legality)
    {
        $this->legalities[] = $legality;

        return $this;
    }

    /**
     * Remove legality
     *
     * @param \AppBundle\Entity\Legality $legality
     */
    public function removeLegality(\AppBundle\Entity\Legality $legality)
    {
        $this->legalities->removeElement($legality);
    }

    /**
     * Get legalities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLegalities()
    {
        return $this->legalities;
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
     * @return Mwl
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
     * @return Mwl
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

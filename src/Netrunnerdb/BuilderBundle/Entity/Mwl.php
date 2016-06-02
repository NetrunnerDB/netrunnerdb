<?php

namespace Netrunnerdb\BuilderBundle\Entity;

/**
 * Mwl
 */
class Mwl implements \Serializable
{
	function serialize() {
		$cards = [];
		foreach($this->slots as $slot) {
			$cards[$slot->getCard()->getCode()] = $slot->getPenalty();
		}
	
		return  [
				'id' => $this->id,
				'active' => $this->active,
				'date_creation' => $this->dateCreation->format('Y-m-d'),
				'date_update' => $this->dateUpdate->format('Y-m-d'),
				'name' => $this->name,
				'start' => $this->dateStart ? $this->dateStart->format('Y-m-d') : null,
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
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add slot
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Mwlslot $slot
     *
     * @return Mwl
     */
    public function addSlot(\Netrunnerdb\BuilderBundle\Entity\Mwlslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Mwlslot $slot
     */
    public function removeSlot(\Netrunnerdb\BuilderBundle\Entity\Mwlslot $slot)
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
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $deck
     *
     * @return Mwl
     */
    public function addDeck(\Netrunnerdb\BuilderBundle\Entity\Deck $deck)
    {
        $this->decks[] = $deck;

        return $this;
    }

    /**
     * Remove deck
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $deck
     */
    public function removeDeck(\Netrunnerdb\BuilderBundle\Entity\Deck $deck)
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
     * @param \Netrunnerdb\BuilderBundle\Entity\Legality $legality
     *
     * @return Mwl
     */
    public function addLegality(\Netrunnerdb\BuilderBundle\Entity\Legality $legality)
    {
        $this->legalities[] = $legality;

        return $this;
    }

    /**
     * Remove legality
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Legality $legality
     */
    public function removeLegality(\Netrunnerdb\BuilderBundle\Entity\Legality $legality)
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

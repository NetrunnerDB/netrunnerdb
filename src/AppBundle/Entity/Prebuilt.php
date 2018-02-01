<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * Prebuilt
 */
class Prebuilt implements Translatable, \Serializable
{
    public function toString()
    {
        return $this->name;
    }
    
    public function serialize()
    {
        $cards = [];
        foreach ($this->slots as $slot) {
            $cards[$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        
        return [
                'code' => $this->code,
                'date_release' => $this->dateRelease ? $this->dateRelease->format('Y-m-d') : null,
                'name' => $this->name,
                'position' => $this->position,
                'cards' => $cards
        ];
    }
    
    public function unserialize($serialized)
    {
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
    private $position;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * @var Side
     */
    private $side;

    /**
     * @var Card
     */
    private $identity;

    /**
     * @var Faction
     */
    private $faction;

    /**
     * @var string
     */
    private $locale = 'en';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return Prebuilt
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
     * @return Prebuilt
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
     *
     * @return Prebuilt
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
     * Set position
     *
     * @param integer $position
     *
     * @return Prebuilt
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Prebuilt
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
     * @return Prebuilt
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

    /**
     * Add slot
     *
     * @param Prebuiltslot $slot
     *
     * @return Prebuilt
     */
    public function addSlot(Prebuiltslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param Prebuiltslot $slot
     */
    public function removeSlot(Prebuiltslot $slot)
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
     * Set side
     *
     * @param Side $side
     *
     * @return Prebuilt
     */
    public function setSide(Side $side = null)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get side
     *
     * @return Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set identity
     *
     * @param Card $identity
     *
     * @return Prebuilt
     */
    public function setIdentity(Card $identity = null)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Get identity
     *
     * @return Card
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Set faction
     *
     * @param Faction $faction
     *
     * @return Prebuilt
     */
    public function setFaction(Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * Get translatableLocale
     *
     * @return string
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}

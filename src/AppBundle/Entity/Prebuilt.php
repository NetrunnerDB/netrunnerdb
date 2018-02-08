<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Prebuilt
 */
class Prebuilt implements NormalizableInterface, TimestampableInterface
{
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
     * @var Collection
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
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name ?: '(unknown)';
    }

    public function normalize()
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

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Prebuilt
     */
    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Prebuilt
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateRelease()
    {
        return $this->dateRelease;
    }

    /**
     * @param \DateTime $dateRelease
     * @return Prebuilt
     */
    public function setDateRelease(\DateTime $dateRelease)
    {
        $this->dateRelease = $dateRelease;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param integer $position
     * @return Prebuilt
     */
    public function setPosition(int $position)
    {
        $this->position = $position;

        return $this;
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
     * @return Prebuilt
     */
    public function setDateCreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return Prebuilt
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Add slot
     * @param Prebuiltslot $slot
     * @return Prebuilt
     */
    public function addSlot(Prebuiltslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     * @param Prebuiltslot $slot
     */
    public function removeSlot(Prebuiltslot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * @return Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @return Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * @param Side $side
     * @return $this
     */
    public function setSide(Side $side)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * @return Card
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param Card $identity
     * @return $this
     */
    public function setIdentity(Card $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @param Faction $faction
     * @return $this
     */
    public function setFaction(Faction $faction)
    {
        $this->faction = $faction;

        return $this;
    }
}

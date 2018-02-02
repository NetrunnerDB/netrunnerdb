<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\AbstractTranslatableEntity;
use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Type
 */
class Type extends AbstractTranslatableEntity implements NormalizableInterface, TimestampableInterface, CodeNameInterface
{
    public function __toString()
    {
        return $this->name ?: '(unknown)';
    }

    public function normalize()
    {
        return [
                'code' => $this->code,
                'name' => $this->name,
                'position' => $this->position,
                'is_subtype' => $this->isSubtype,
                'side_code' => $this->side ? $this->side->getCode() : null
        ];
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
     * @var Side
     */
    private $side;

    /**
     * @var boolean
     */
    private $isSubtype;

    /**
     * @var \DateTime
     */
    private $dateCreation;
    
    /**
     * @var \DateTime
     */
    private $dateUpdate;
    
    /**
     * @var integer
     */
    private $position;

    /**
     * @var Collection
     */
    private $cards;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
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
     * Set text
     *
     * @param string $name
     * @return Type
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Type
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Type
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
     * @return Type
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
     * Set isSubtype
     *
     * @param boolean $isSubtype
     *
     * @return Type
     */
    public function setIsSubtype($isSubtype)
    {
        $this->isSubtype = $isSubtype;

        return $this;
    }

    /**
     * Get isSubtype
     *
     * @return boolean
     */
    public function getIsSubtype()
    {
        return $this->isSubtype;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Type
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
     * Set side
     *
     * @param Side $side
     * @return $this
     */
    public function setSide(Side $side)
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
     * Add cards
     *
     * @param Card $cards
     * @return Type
     */
    public function addCard(Card $cards)
    {
        $this->cards[] = $cards;
    
        return $this;
    }
    
    /**
     * Remove cards
     *
     * @param Card $cards
     */
    public function removeCard(Card $cards)
    {
        $this->cards->removeElement($cards);
    }
    
    /**
     * Get cards
     *
     * @return Collection
     */
    public function getCards()
    {
        return $this->cards;
    }
}

<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\AbstractTranslatableEntity;
use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Cycle
 */
class Cycle extends AbstractTranslatableEntity implements NormalizableInterface, TimestampableInterface, CodeNameInterface
{
    public function __toString()
    {
        return $this->name;
    }
    
    public function normalize()
    {
        return [
                'code' => $this->code,
                'name' => $this->name,
                'position' => $this->position,
                'size' => $this->size,
                'rotated' => $this->rotated
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
     * @var Collection|Rotation[]
     * @ORM\ManyToMany(targetEntity="Rotation", mappedBy="cycles")
     */
    protected $rotations;

    /** @param Collection|Rotation[] $rotations */
    public function setRotations(Collection $rotations)
    {
        $this->clearRotations();
        foreach ($rotations as $rotation) {
            $this->addRotation($rotation);
        }

        return $this;
    }

    public function addRotation(Rotation $rotation)
    {
        if ($this->rotations->contains($rotation) === false) {
            $this->rotations->add($rotation);
            $rotation->addCycle($this);
        }

        return $this;
    }

    /** @return Collection|Rotation[] */
    public function getRotations()
    {
        return $this->rotations;
    }

    public function removeRotation(Rotation $rotation)
    {
        if ($this->rotations->contains($rotation)) {
            $this->rotations->removeElement($rotation);
            $rotation->removeCycle($this);
        }

        return $this;
    }

    public function clearRotations()
    {
        foreach ($this->getRotations() as $rotation) {
            $this->removeRotation($rotation);
        }
        $this->rotations->clear();

        return $this;
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
     * @var Collection|Pack[]
     */
    private $packs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->packs = new ArrayCollection();
        $this->rotations = new ArrayCollection();
    }
    
    /**
     * Add packs
     *
     * @param Pack $packs
     * @return Cycle
     */
    public function addPack(Pack $packs)
    {
        $this->packs[] = $packs;
    
        return $this;
    }

    /**
     * Remove packs
     *
     * @param Pack $packs
     */
    public function removePack(Pack $packs)
    {
        $this->packs->removeElement($packs);
    }

    /**
     * @return Pack[]|Collection
     */
    public function getPacks()
    {
        return $this->packs;
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

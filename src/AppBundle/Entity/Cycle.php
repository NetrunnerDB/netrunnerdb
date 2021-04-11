<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Cycle
 */
class Cycle implements NormalizableInterface, TimestampableInterface, CodeNameInterface
{
    /**
     * @var Collection|Rotation[]
     * @ORM\ManyToMany(targetEntity="Rotation", mappedBy="rotated")
     */
    protected $rotations;

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
     * @var Collection|Pack[]
     */
    private $packs;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->packs = new ArrayCollection();
        $this->rotations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name ?: '(unknown)';
    }

    /**
     * @return array
     */
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
     * @return Rotation[]|ArrayCollection|Collection
     */
    public function getRotations()
    {
        return $this->rotations;
    }

    /**
     * @param Collection $rotations
     * @return $this
     */
    public function setRotations(Collection $rotations)
    {
        $this->clearRotations();
        foreach ($rotations as $rotation) {
            $this->addRotation($rotation);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearRotations()
    {
        foreach ($this->getRotations() as $rotation) {
            $this->removeRotation($rotation);
        }
        $this->rotations->clear();

        return $this;
    }

    /**
     * @param Rotation $rotation
     * @return $this
     */
    public function removeRotation(Rotation $rotation)
    {
        if ($this->rotations->contains($rotation)) {
            $this->rotations->removeElement($rotation);
            $rotation->removeCycle($this);
        }

        return $this;
    }

    /**
     * @param Rotation $rotation
     * @return $this
     */
    public function addRotation(Rotation $rotation)
    {
        if ($this->rotations->contains($rotation) === false) {
            $this->rotations->add($rotation);
            $rotation->addCycle($this);
        }

        return $this;
    }

    /**
     * @return int
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
     * @return $this
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
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRotated()
    {
        return $this->rotated;
    }

    /**
     * @param bool $rotated
     * @return $this
     */
    public function setRotated(bool $rotated)
    {
        $this->rotated = $rotated;

        return $this;
    }

    /**
     * @param Pack $packs
     * @return $this
     */
    public function addPack(Pack $packs)
    {
        $this->packs[] = $packs;

        return $this;
    }

    /**
     * @param Pack $packs
     */
    public function removePack(Pack $packs)
    {
        $this->packs->removeElement($packs);
    }

    /**
     * @return Pack[]|ArrayCollection|Collection
     */
    public function getPacks()
    {
        return $this->packs;
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
     * @return $this
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
     * @return $this
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }
}

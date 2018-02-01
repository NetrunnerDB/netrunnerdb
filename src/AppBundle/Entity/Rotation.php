<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Description of Rotation
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class Rotation implements \Serializable
{
    public function toString()
    {
        return $this->name;
    }

    public function serialize()
    {
        $cycles = [];
        foreach ($this->cycles as $cycle) {
            $cycles[] = $cycle->getCode();
        }

        return  [
            'id' => $this->id,
            'date_creation' => $this->dateCreation ? $this->dateCreation->format('c') : null,
            'date_update' => $this->dateUpdate ? $this->dateUpdate->format('c') : null,
            'code' => $this->code,
            'name' => $this->name,
            'date_start' => $this->dateStart ? $this->dateStart->format('Y-m-d') : null,
            'cycles' => $cycles
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
    private $dateStart;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var Collection|Decklist[]
     * @ORM\OneToMany(targetEntity="Decklist", mappedBy="rotation")
     */
    private $decklists;

    /** @param Collection|Decklist[] $decklists */
    public function setDecklists(Collection $decklists)
    {
        $this->clearDecklists();
        foreach ($decklists as $decklist) {
            $this->addDecklist($decklist);
        }

        return $this;
    }

    public function addDecklist(Decklist $decklist)
    {
        if ($this->decklists->contains($decklist) === false) {
            $this->decklists->add($decklist);
            $decklist->setRotation($this);
        }

        return $this;
    }

    /** @return Collection|Decklist[] */
    public function getDecklists()
    {
        return $this->decklists;
    }

    public function removeDecklist(Decklist $decklist)
    {
        if ($this->decklists->contains($decklist)) {
            $this->decklists->removeElement($decklist);
            $decklist->setRotation(null);
        }

        return $this;
    }

    public function clearDecklists()
    {
        foreach ($this->getDecklists() as $decklist) {
            $this->removeDecklist($decklist);
        }
        $this->decklists->clear();

        return $this;
    }

    /**
     * @var Collection|Cycle[]
     * @ORM\ManyToMany(targetEntity="Cycle", inversedBy="rotations")
     * @ORM\JoinTable(
     *     name="rotation_cycle",
     *     joinColumns={
     *         @ORM\JoinColumn(name="rotation_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="cycle_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $cycles;

    public function __construct()
    {
        $this->cycles = new ArrayCollection();
    }

    /** @param Collection|Cycle[] $cycles */
    public function setCycles(Collection $cycles)
    {
        $this->clearCycles();
        foreach ($cycles as $cycle) {
            $this->addCycle($cycle);
        }

        return $this;
    }

    public function addCycle(Cycle $cycle)
    {
        if ($this->cycles->contains($cycle) === false) {
            $this->cycles->add($cycle);
            $cycle->addRotation($this);
        }

        return $this;
    }

    /** @return Collection|Cycle[] */
    public function getCycles()
    {
        return $this->cycles;
    }

    public function removeCycle(Cycle $cycle)
    {
        if ($this->cycles->contains($cycle)) {
            $this->cycles->removeElement($cycle);
            $cycle->removeRotation($this);
        }

        return $this;
    }

    public function clearCycles()
    {
        foreach ($this->getCycles() as $cycle) {
            $this->removeCycle($cycle);
        }
        $this->cycles->clear();

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStart()
    {
        return $this->dateStart;
    }

    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }
}

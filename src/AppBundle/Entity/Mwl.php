<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Mwl
 */
class Mwl implements NormalizableInterface, TimestampableInterface
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
    private $dateStart;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var array
     */
    private $cards;

    /**
     * @var Collection
     */
    private $legalities;

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
        $this->active = false;
    }

    public function __toString()
    {
        return $this->name ?: '(unknown)';
    }

    public function normalize()
    {
        return [
            'id'            => $this->id,
            'date_creation' => $this->dateCreation ? $this->dateCreation->format('c') : null,
            'date_update'   => $this->dateUpdate ? $this->dateUpdate->format('c') : null,
            'code'          => $this->code,
            'name'          => $this->name,
            'active'        => $this->active,
            'date_start'    => $this->dateStart ? $this->dateStart->format('Y-m-d') : null,
            'cards'         => $this->cards,
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
     * @return Mwl
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
     * @return Mwl
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     * @return Mwl
     */
    public function setDateStart(\DateTime $dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return Mwl
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Add legality
     * @param Legality $legality
     * @return Mwl
     */
    public function addLegality(Legality $legality)
    {
        $this->legalities[] = $legality;

        return $this;
    }

    /**
     * Remove legality
     * @param Legality $legality
     */
    public function removeLegality(Legality $legality)
    {
        $this->legalities->removeElement($legality);
    }

    /**
     * @return Collection
     */
    public function getLegalities()
    {
        return $this->legalities;
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
     * @return Mwl
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
     * @return Mwl
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return array
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @param array $cards
     * @return Mwl
     */
    public function setCards(array $cards): self
    {
        $this->cards = $cards;

        return $this;
    }

    public function getBannedCardCodes(): array {
        $bannedCardCodes = [];
        foreach ($this->cards as $code => $detail) {
            if (isset($detail['deck_limit']) && $detail['deck_limit'] == 0) {
                $bannedCardCodes[] = $code;
            }
        }
        return $bannedCardCodes;
    }
}

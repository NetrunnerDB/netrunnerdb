<?php

namespace AppBundle\Entity;

/**
 * Deckchange
 */
class Deckchange
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var string
     */
    private $variation;

    /**
     * @var Deck
     */
    private $deck;

    /**
     * @var boolean
     */
    private $saved;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function setDatecreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return string
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param string $variation
     * @return $this
     */
    public function setVariation(string $variation)
    {
        $this->variation = $variation;

        return $this;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param Deck $deck
     * @return $this
     */
    public function setDeck(Deck $deck)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSaved()
    {
        return $this->saved;
    }

    /**
     * @param boolean $saved
     * @return Deckchange
     */
    public function setSaved(bool $saved)
    {
        $this->saved = $saved;

        return $this;
    }
}

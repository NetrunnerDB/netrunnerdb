<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\TimestampableInterface;

/**
 * @author Alsciende <alsciende@icloud.com>
 */
class Ruling implements TimestampableInterface
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
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var string
     */
    private $rawtext;

    /**
     * @var string
     */
    private $text;

    /**
     * @var Card
     */
    private $card;

    /**
     * @var User
     */
    private $user;

    public function getId()
    {
        return $this->id;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    public function getRawtext()
    {
        return $this->rawtext;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getCard()
    {
        return $this->card;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDateCreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;
    }

    public function setRawtext($rawtext)
    {
        $this->rawtext = $rawtext;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function setCard(Card $card)
    {
        $this->card = $card;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}

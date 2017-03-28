<?php

namespace AppBundle\Entity;

/**
 * @author Alsciende <alsciende@icloud.com>
 */
class Ruling
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
     * @var \AppBundle\Entity\Card
     */
    private $card;

    /**
     * @var \AppBundle\Entity\User
     */
    private $user;

    function getId ()
    {
        return $this->id;
    }

    function getDateCreation ()
    {
        return $this->dateCreation;
    }

    function getDateUpdate ()
    {
        return $this->dateUpdate;
    }

    function getRawtext ()
    {
        return $this->rawtext;
    }

    function getText ()
    {
        return $this->text;
    }

    function getCard ()
    {
        return $this->card;
    }

    function getUser ()
    {
        return $this->user;
    }

    function setId ($id)
    {
        $this->id = $id;
    }

    function setDateCreation (\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    function setDateUpdate (\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;
    }

    function setRawtext ($rawtext)
    {
        $this->rawtext = $rawtext;
    }

    function setText ($text)
    {
        $this->text = $text;
    }

    function setCard (\AppBundle\Entity\Card $card)
    {
        $this->card = $card;
    }

    function setUser (\AppBundle\Entity\User $user)
    {
        $this->user = $user;
    }


}

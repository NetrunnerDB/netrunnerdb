<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

/**
 * Claim
 */
class Claim
{

    /**
     * @var integer
     * @Serializer\Expose
     */
    private $id;

    /**
     * @var string
     * @Serializer\Expose
     */
    private $name;

    /**
     * @var string
     * @Serializer\Expose
     */
    private $url;

    /**
     * @var integer
     * @Serializer\Expose
     */
    private $rank;

    /**
     * @var \AppBundle\Entity\Decklist
     * @Serializer\Exclude
     */
    private $decklist;

    /**
     * @var \AppBundle\Entity\Client
     * @Serializer\Exclude
     */
    private $client;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Claim
     */
    public function setName ($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Claim
     */
    public function setUrl ($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl ()
    {
        return $this->url;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     *
     * @return Claim
     */
    public function setRank ($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank ()
    {
        return $this->rank;
    }

    /**
     * Set decklist
     *
     * @param \AppBundle\Entity\Decklist $decklist
     *
     * @return Claim
     */
    public function setDecklist (\AppBundle\Entity\Decklist $decklist = null)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return \AppBundle\Entity\Decklist
     */
    public function getDecklist ()
    {
        return $this->decklist;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return Claim
     */
    public function setClient (\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client
     */
    public function getClient ()
    {
        return $this->client;
    }

}

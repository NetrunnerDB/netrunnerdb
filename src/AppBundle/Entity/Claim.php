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
     * @var integer
     * @Serializer\Expose
     */
    private $participants;

    /**
     * @var Decklist
     * @Serializer\Exclude
     */
    private $decklist;

    /**
     * @var Client
     * @Serializer\Exclude
     */
    private $client;

    /**
     * @var User
     * @Serializer\Exclude
     */
    private $user;

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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     * @return $this
     */
    public function setRank(int $rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @return int
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param int $participants
     * @return $this
     */
    public function setParticipants(int $participants)
    {
        $this->participants = $participants;

        return $this;
    }

    /**
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * @param Decklist $decklist
     * @return $this
     */
    public function setDecklist(Decklist $decklist)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    protected $name;

    /**
     * @var Collection
     */
    private $claims;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param Claim $claim
     * @return $this
     */
    public function addClaim(Claim $claim)
    {
        $this->claims[] = $claim;

        return $this;
    }

    /**
     * @param Claim $claim
     */
    public function removeClaim(Claim $claim)
    {
        $this->claims->removeElement($claim);
    }

    /**
     * @return Collection
     */
    public function getClaims()
    {
        return $this->claims;
    }
}

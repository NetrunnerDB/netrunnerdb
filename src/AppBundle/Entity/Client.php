<?php

namespace AppBundle\Entity;

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

    public function __construct()
    {
        parent::__construct();
    }
    
    protected $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $claims;


    /**
     * Add claim
     *
     * @param \AppBundle\Entity\Claim $claim
     *
     * @return Client
     */
    public function addClaim(\AppBundle\Entity\Claim $claim)
    {
        $this->claims[] = $claim;

        return $this;
    }

    /**
     * Remove claim
     *
     * @param \AppBundle\Entity\Claim $claim
     */
    public function removeClaim(\AppBundle\Entity\Claim $claim)
    {
        $this->claims->removeElement($claim);
    }

    /**
     * Get claims
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClaims()
    {
        return $this->claims;
    }
}

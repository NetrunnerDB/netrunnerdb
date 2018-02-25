<?php

namespace AppBundle\Service;

use AppBundle\Entity\Cycle;
use AppBundle\Entity\Pack;
use AppBundle\Repository\CycleRepository;
use AppBundle\Repository\PackRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of RepositoryFactory
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RepositoryFactory
{
    /** @var ManagerRegistry $registry */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $class
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository(string $class)
    {
        $objectManager = $this->registry->getManagerForClass($class);

        if ($objectManager instanceof ObjectManager) {
            return $objectManager->getRepository($class);
        }

        throw new \LogicException('No manager defined for class ' . $class);
    }

    /**
     * @return PackRepository
     */
    public function getPackRepository()
    {
        $repository = $this->getRepository(Pack::class);

        if ($repository instanceof PackRepository) {
            return $repository;
        }

        throw new \LogicException('Doctrine manager returned wrong repository.');
    }

    /**
     * @return CycleRepository
     */
    public function getCycleRepository()
    {
        $repository = $this->getRepository(Cycle::class);

        if ($repository instanceof CycleRepository) {
            return $repository;
        }

        throw new \LogicException('Doctrine manager returned wrong repository.');
    }
}
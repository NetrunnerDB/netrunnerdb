<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManager;

class CycleRepository extends TranslatableRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Cycle'));
    }
}

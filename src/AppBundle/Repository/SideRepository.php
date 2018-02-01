<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManager;

class SideRepository extends TranslatableRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Side'));
    }
}

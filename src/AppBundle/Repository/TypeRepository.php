<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManager;

class TypeRepository extends TranslatableRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Type'));
    }
}

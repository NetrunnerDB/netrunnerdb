<?php 

namespace AppBundle\Repository;

class TypeRepository extends TranslatableRepository
{
    public function __construct($entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Type'));
    }
}

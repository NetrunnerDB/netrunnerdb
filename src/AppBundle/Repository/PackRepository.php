<?php 

namespace AppBundle\Repository;

class PackRepository extends TranslatableRepository
{
    public function __construct($entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Pack'));
    }
}

<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;

class TypeRepository extends TranslatableRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Type'));
    }
}

<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;

class PackRepository extends TranslatableRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Pack'));
    }
}

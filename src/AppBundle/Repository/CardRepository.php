<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;

class CardRepository extends TranslatableRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Card'));
    }

    public function findAll()
    {
        return $this->findBy([], ['code' => 'ASC']);
    }
}

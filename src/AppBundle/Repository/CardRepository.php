<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManager;

class CardRepository extends TranslatableRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Card'));
    }

    public function findAll()
    {
        return $this->findBy([], ['code' => 'ASC']);
    }
}

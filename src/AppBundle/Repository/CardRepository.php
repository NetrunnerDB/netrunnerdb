<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Faction;
use AppBundle\Service\CardsData;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class CardRepository extends EntityRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Card'));
    }

    public function findAll()
    {
        return $this->findBy([], ['code' => 'ASC']);
    }

    public function findByFaction(Faction $faction)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('PARTIAL c.{id, code, title}')
            ->from('AppBundle:Card', 'c')
            ->join('c.pack', 'p')
            ->join('c.type', 't')
            ->where('c.faction=:faction')
            ->setParameter('faction', $faction)
            ->andWhere('t.code=:type')
            ->setParameter('type', 'identity')
            ->andWhere('p.dateRelease is not null');

        $identities = $queryBuilder->getQuery()->getResult();

        return $identities;
    }
}
<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Cycle;
use AppBundle\Entity\Pack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class PackRepository extends EntityRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Pack'));
    }

    /**
     * @param Cycle $cycle
     * @return Pack[]
     */
    public function findByCycleWithCardCount(Cycle $cycle): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Pack::class, 'p');
        $rsm->addScalarResult('cards', 'count', 'integer');

        $selectClause = $rsm->generateSelectClause([
            'p' => 'p',
        ]);

        $sql = 'SELECT ' . $selectClause . ', count(c.id) as cards from pack p
                    left join card c on c.pack_id=p.id
                    where p.cycle_id = ?
                    group by p.id
                    order by p.position';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $cycle->getId());

        return array_map(function ($item) {
            /** @var Pack $pack */
            $pack = $item[0];
            $pack->setCardCount($item['count']);

            return $pack;
        }, $query->getResult());
    }
}

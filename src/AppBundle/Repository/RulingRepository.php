<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of RulingRepository
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RulingRepository extends EntityRepository
{
    public function findAllSortedByCardCode()
    {
        return $this
            ->_em
            ->createQuery(
                'SELECT r FROM AppBundle:Ruling r JOIN r.card c ORDER BY c.code ASC'
            )
            ->getResult();
    }
}

<?php

namespace AppBundle\Repository;

/**
 * Description of RulingRepository
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RulingRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllSortedByCardCode()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT r FROM AppBundle:Ruling r JOIN r.card c ORDER BY c.code ASC'
            )
            ->getResult();
    }
}

<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Logging\EchoSQLLogger;

class MWLRepository extends EntityRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\MWL'));
    }

    private function findActiveMWLs()
    {
        return $this->findBy(['active' => 1], ['dateStart' => 'DESC']);
    }

    public function getBannedCardCodes() {
        $activeMWLs = $this->findActiveMWLs();
        $result = [];
        foreach ($activeMWLs as $mwl) {
            $result = array_merge($result, $mwl->getBannedCardCodes());
        }

        return $result;
    }
}

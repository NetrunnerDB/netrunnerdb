<?php 

namespace Netrunnerdb\CardsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CycleRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('Netrunnerdb\CardsBundle\Entity\Cycle'));
	}
}

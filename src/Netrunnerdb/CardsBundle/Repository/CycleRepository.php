<?php 

namespace Netrunnerdb\CardsBundle\Repository;

class CycleRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('Netrunnerdb\CardsBundle\Entity\Cycle'));
	}
}

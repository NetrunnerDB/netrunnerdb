<?php 

namespace AppBundle\Repository;

class FactionRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Faction'));
	}
}

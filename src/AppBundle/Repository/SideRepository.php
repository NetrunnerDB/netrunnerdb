<?php 

namespace AppBundle\Repository;

class SideRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Side'));
	}
}

<?php 

namespace AppBundle\Repository;

class PackRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Pack'));
	}
}

<?php 

namespace AppBundle\Repository;

class TypeRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Type'));
	}
}

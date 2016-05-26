<?php 

namespace Netrunnerdb\CardsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PackRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('Netrunnerdb\CardsBundle\Entity\Pack'));
	}
}

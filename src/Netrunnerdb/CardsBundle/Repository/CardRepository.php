<?php 

namespace Netrunnerdb\CardsBundle\Repository;

class CardRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('Netrunnerdb\CardsBundle\Entity\Card'));
	}
	
	public function findAll()
	{
		return $this->findBy(array(), array('code' => 'ASC'));
	}
}

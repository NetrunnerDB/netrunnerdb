<?php

namespace Netrunnerdb\CardsBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

class SideManager extends TranslatableManager
{
	protected $class;
	protected $orm;
	protected $repo;
	
	public function __construct(ObjectManager $orm , $class)
	{
		$this->orm = $orm;
		$this->repo = $orm->getRepository($class);
	
		$metaData = $orm->getClassMetadata($class);
		$this->class = $metaData->getName();
	}

	public function getClass()
	{
		return $this->class;
	}
}
<?php

namespace Netrunnerdb\CardsBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;

/**
 * Translatable entity manager
 */
class TranslatableManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var EntityRepository
	 */
	protected $repository;

	/**
	 * @var string Class name
	 */
	protected $class;


	/**
	 * Constructor
	 *
	 * @param EntityManager $em    Entity manager
	 * @param string        $class Class name
	 */
	public function __construct(EntityManager $em, $class)
	{
		$this->class      = $class;
		$this->em         = $em;
		$this->repository = $em->getRepository($this->class);
	}

	/**
	 * Sets the repository request default locale
	 *
	 * @param ContainerInterface|null $container
	 *
	 * @throws \InvalidArgumentException if repository is not an instance of TranslatableRepository
	 */
	public function setRepositoryLocale($container)
	{
		if (null !== $container) {
			if (!$this->repository instanceof TranslatableRepository) {
				throw new \InvalidArgumentException('A TranslatableManager needs to be linked with a TranslatableRepository to sets default locale.');
			}

			if ($container->isScopeActive('request')) {
				$locale = $container->get('request')->getLocale();
				$this->repository->setDefaultLocale($locale);
			}
		}
	}
}
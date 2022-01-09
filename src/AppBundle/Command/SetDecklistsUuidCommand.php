<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Update UUIDs for any decklists that are missing them. 
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class SetDecklistsUuidCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
                ->setName('app:uuid:decklists')
                ->setDescription('Set a UUID for any decklist missing it.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$sql = "UPDATE decklist "
			. "SET uuid = LOWER(CONCAT("
			. "HEX(RANDOM_BYTES(4)), '-', "
			. "HEX(RANDOM_BYTES(2)), '-4', "
			. "SUBSTR(HEX(RANDOM_BYTES(2)), 2, 3), '-', "
			. "CONCAT(HEX(FLOOR(ASCII(RANDOM_BYTES(1)) / 64)+8), SUBSTR(HEX(RANDOM_BYTES(2)), 2, 3)), "
			. "'-', HEX(RANDOM_BYTES(6))))"
			. " WHERE uuid IS NULL";

        $this->entityManager->getConnection()->executeQuery($sql);

        $output->writeln("<info>Done</info>");
    }
}

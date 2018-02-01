<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class LegalityDecklistsRotationCommand extends ContainerAwareCommand
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
                ->setName('nrdb:legality:decklists-rotation')
                ->setDescription('Compute decklist legality regarding rotations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = "UPDATE decklist d SET d.is_legal=0 WHERE d.is_legal=1"
                . " AND EXISTS(SELECT *"
                . " FROM decklistslot s"
                . " JOIN card c ON c.id=s.card_id"
                . " JOIN pack p ON p.id=c.pack_id"
                . " JOIN cycle y ON y.id=p.cycle_id"
                . " WHERE y.rotated=1"
                . " AND d.id=s.decklist_id)";
        
        $this->entityManager->getConnection()->executeQuery($sql);

        $output->writeln("<info>Done</info>");
    }
}

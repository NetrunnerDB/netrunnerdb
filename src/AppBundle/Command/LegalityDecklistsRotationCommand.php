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
                ->setName('app:legality:decklists-rotation')
                ->setDescription('Compute decklist legality regarding rotations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = "UPDATE decklist d
            JOIN decklistslot s ON d.id = s.decklist_id
            JOIN card c ON s.card_id = c.id
            JOIN pack p ON c.pack_id = p.id
            JOIN cycle y ON p.cycle_id = y.id
            SET d.is_legal = 1
            WHERE d.is_legal = 0
            AND y.rotated = 1
            AND EXISTS (
                SELECT *
                FROM card nc
                JOIN pack np ON nc.pack_id = np.id
                JOIN cycle ny ON np.cycle_id = ny.id
                WHERE nc.id <> c.id
                AND nc.title = c.title
                AND np.id >= p.id
                AND ny.rotated = 0);";

        $this->entityManager->getConnection()->executeQuery($sql);

        $output->writeln("<info>Done</info>");
    }
}

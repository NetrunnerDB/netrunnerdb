<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Description of LegalityDecklistsMwlCommand
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class LegalityDecklistsMwlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
                ->setName('nrdb:legality:decklists-mwl')
                ->setDescription('Compute decklist legality regarding MWL')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $sql = "UPDATE decklist d SET d.is_legal=0 WHERE d.is_legal=1"
                . " AND EXISTS(SELECT *"
                . " FROM legality l"
                . " JOIN mwl m ON m.id=l.mwl_id"
                . " WHERE m.active=1"
                . " AND l.is_legal=0"
                . " AND d.id=l.decklist_id)";
        
        $entityManager->getConnection()->executeQuery($sql);

        $output->writeln("<info>Done</info>");
    }
}

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
    
    protected function configure ()
    {
        $this
                ->setName('nrdb:legality:decklists-mwl')
                ->setDescription('Compute decklist legality regarding MWL')
        ;
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $countDql = "SELECT COUNT(DISTINCT d) FROM AppBundle:Decklist d"
                . " JOIN AppBundle:Legality l WITH l.decklist=d"
                . " JOIN AppBundle:Mwl m WITH l.mwl=m"
                . " WHERE m.active=true"
                . " AND l.isLegal=false"
                . " AND d.isLegal=true";
        $countQuery = $entityManager->createQuery($countDql);
        $count = $countQuery->getSingleResult()[1];
        $output->writeln("<comment>Found $count decklists to change</comment>");

        if (!$count) {
            return;
        }

        $progress = new ProgressBar($output, $count);
        $progress->setRedrawFrequency(10);
        $progress->start();

        $fetchDql = str_replace('COUNT(DISTINCT d)', 'DISTINCT d', $countDql);
        $fetchQuery = $entityManager->createQuery($fetchDql)->setMaxResults(1);
        while ($count--) {
            /* @var $decklist \AppBundle\Entity\Decklist */
            $decklist = $fetchQuery->getSingleResult();
            $decklist->setIsLegal(false);
            $entityManager->flush();
            $entityManager->detach($decklist);
            $progress->advance();
        }

        $progress->finish();
        $output->writeln("<info>Done</info>");
    }

}

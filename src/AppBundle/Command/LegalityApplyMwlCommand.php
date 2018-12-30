<?php

namespace AppBundle\Command;

use AppBundle\Entity\Decklist;
use AppBundle\Entity\Mwl;
use AppBundle\Service\Judge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Entity\Legality;

class LegalityApplyMwlCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Judge $judge */
    private $judge;

    public function __construct(EntityManagerInterface $entityManager, Judge $judge)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->judge = $judge;
    }

    protected function configure()
    {
        $this
            ->setName('app:legality:apply-mwl')
            ->setDescription('Compute decklist legality for a MWL')
            ->addArgument(
                'mwl_code',
                InputArgument::REQUIRED,
                'Code of the MWL'
            )
            ->addOption(
                'decklist',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Id of the decklist'
            )

            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mwl_code = $input->getArgument('mwl_code');
        $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwl_code]);

        if (!$mwl instanceof Mwl) {
            throw new \Exception("MWL not found");
        }

        $decklist_id = $input->getOption('decklist');
        if ($decklist_id) {
            $decklist = $this->entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
            if (!$decklist instanceof Decklist) {
                throw new \Exception("Decklist not found");
            }
            $legality = $this->entityManager->getRepository('AppBundle:Legality')->findOneBy(['mwl' => $mwl, 'decklist' => $decklist]);
            if (!$legality instanceof Legality) {
                $legality = new Legality();
                $legality->setDecklist($decklist);
                $legality->setMwl($mwl);
                $this->entityManager->persist($legality);
            }
            $this->judge->computeLegality($legality);
            $this->entityManager->flush();
            if ($legality->getIsLegal()) {
                $output->writeln("<info>Done. Decklist is legal.</info>");
            } else {
                $output->writeln("<info>Done. Decklist is NOT legal.</info>");
            }
            return;
        }

        $countDql = "SELECT COUNT(d)
            FROM AppBundle:Decklist d
            WHERE NOT EXISTS (
                SELECT l
                FROM AppBundle:Legality l
                WHERE l.decklist=d AND l.mwl=?1
            )
            ORDER BY d.id DESC";
        $countQuery = $this->entityManager->createQuery($countDql)->setParameter(1, $mwl);
        $count = $countQuery->getSingleResult()[1];
        $output->writeln("<comment>Found $count decklists to analyze</comment>");

        if (!$count) {
            return;
        }

        $progress = new ProgressBar($output, $count);
        $progress->setRedrawFrequency(10);
        $progress->start();

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $batchSize = 100;
        $i = 0;

        $fetchDql = str_replace('COUNT(d)', 'd', $countDql);
        $fetchQuery = $this->entityManager->createQuery($fetchDql)->setParameter(1, $mwl);
        $iterableResult = $fetchQuery->iterate();
        foreach ($iterableResult as $row) {
            $mwls = $this->entityManager->getRepository('AppBundle:Mwl')->findAll();

            foreach ($mwls as $m) {
                $legality = new Legality();
                $legality->setDecklist($row[0]);
                $legality->setMwl($m);
                $this->judge->computeLegality($legality);
                $this->entityManager->persist($legality);
            }

            if (($i % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $progress->advance();
            ++$i;
        }

        $progress->finish();
        $output->writeln("\n<info>Done</info>");
        $this->entityManager->flush();
    }
}

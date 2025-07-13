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
            ->addOption(
                'mwl_code',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Code of the MWL. If not provided, the command will iterate over each item in the mwl table.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mwlCodes = [];

        $mwl_code = $input->getOption('mwl_code');
        if ($mwl_code) {
            $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwl_code]);

            if (!$mwl instanceof Mwl) {
                throw new \Exception("MWL not found");
            }
            $mwlCodes[] = $mwl->getCode();
        } else {
            $mwlEntities = $this->entityManager->getRepository('AppBundle:Mwl')->findBy([]);
            foreach ($mwlEntities as $mwl) {
                $mwlCodes[] = $mwl->getCode();
            }
        }

        foreach ($mwlCodes as $mwlCode) {
            $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwlCode]);

            $output->writeln("<info>Applying legality for MWL $mwlCode...</info>");

            // Find all decklists that do not have a legality present for the given MWL.
            $countSql = "SELECT COUNT(id) FROM decklist WHERE id NOT IN (SELECT decklist_id FROM legality WHERE mwl_id = ?)";
            $count = $this->entityManager->getConnection()->executeQuery($countSql, [$mwl->getId()])->fetchColumn(0);
            $output->writeln("<comment>Found $count decklists to analyze</comment>");

            if (!$count) {
                continue;
            }


            $fetchDql = "SELECT id FROM decklist WHERE id NOT IN (SELECT decklist_id FROM legality WHERE mwl_id = ?)";
            $rows = $this->entityManager->getConnection()->executeQuery($fetchDql, [$mwl->getId()])->fetchAll();

            if (empty($rows)) {
                $output->writeln("Decklist id fetch returned no results");
                continue;
            }
            $progress = new ProgressBar($output, $count);
            $batchSize = 100;
            $progress->setRedrawFrequency($batchSize);
            $progress->start();

            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
            $i = 0;
            foreach ($rows as $row) {
                $decklist = $this->entityManager->getRepository('AppBundle:Decklist')->find($row['id']);
                $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwlCode]);
                $legality = new Legality();
                $legality->setDecklist($decklist);
                $legality->setMwl($mwl);
                $this->judge->computeLegality($legality);
                $this->entityManager->persist($legality);

                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }

                $progress->advance();
                ++$i;
            }
            $progress->finish();
        }

        $output->writeln("\n<info>Done</info>");
        $this->entityManager->flush();
    }
}

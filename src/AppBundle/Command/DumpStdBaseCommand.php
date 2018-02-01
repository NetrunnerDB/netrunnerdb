<?php

namespace AppBundle\Command;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Repository\TranslatableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DumpStdBaseCommand extends ContainerAwareCommand
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
        ->setName('nrdb:dump:std:base')
        ->setDescription('Dump Base data for a Locale')
        ->addArgument(
                'entityName',
                InputArgument::REQUIRED,
                "Entity (cycle, pack, faction, type, side)"
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityName = $input->getArgument('entityName');
        $entityFullName = 'AppBundle:'.ucfirst($entityName);

        /** @var TranslatableRepository $repository */
        $repository = $this->entityManager->getRepository($entityFullName);
        
        $qb = $repository->createQueryBuilder('e')->orderBy('e.code');
        
        $result = $repository->getResult($qb);
        
        $arr = [];

        /** @var NormalizableInterface $record */
        foreach ($result as $record) {
            $arr[] = $record->normalize();
        }
        
        $output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $output->writeln("");
    }
}

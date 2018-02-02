<?php

namespace AppBundle\Command;

use AppBundle\Behavior\Entity\CodeNameInterface;
use AppBundle\Repository\TranslatableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DumpTransBaseCommand extends ContainerAwareCommand
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
        ->setName('app:dump:trans:base')
        ->setDescription('Dump Translations of Base data for a Locale')
        ->addArgument(
                'entityName',
                InputArgument::REQUIRED,
                "Entity (cycle, pack, faction, type, side)"
        )
        ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                "Locale"
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getArgument('locale');
        $entityName = $input->getArgument('entityName');
        $entityFullName = 'AppBundle:'.ucfirst($entityName);

        /** @var TranslatableRepository $repository */
        $repository = $this->entityManager->getRepository($entityFullName);
        
        $qb = $repository->setDefaultLocale($locale)->createQueryBuilder('e')->orderBy('e.code');
        
        $result = $repository->getResult($qb);
        
        $arr = [];

        foreach ($result as $record) {
            if ($record instanceof CodeNameInterface) {
                $arr[] = [
                    "code" => $record->getCode(),
                    "name" => $record->getName(),
                ];
            }
        }
        
        $output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $output->writeln("");
    }
}

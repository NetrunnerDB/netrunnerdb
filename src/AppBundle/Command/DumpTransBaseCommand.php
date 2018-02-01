<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DumpTransBaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('nrdb:dump:trans:base')
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
        
        /* @var $repository \AppBundle\Repository\TranslatableRepository */
        $repository = $this->getContainer()->get('doctrine')->getManager()->getRepository($entityFullName);
        
        $qb = $repository->setDefaultLocale($locale)->createQueryBuilder('e')->orderBy('e.code');
        
        $result = $repository->getResult($qb);
        
        $arr = [];
        
        foreach ($result as $record) {
            $data = [
                    "code" => $record->getCode(),
                    "name" => $record->getName()
            ];
            $arr[] = $data;
        }
        
        $output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $output->writeln("");
    }
}

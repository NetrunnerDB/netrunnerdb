<?php

namespace AppBundle\Command;

use AppBundle\Entity\Pack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\BufferedOutput;

class ExportStdCommand extends ContainerAwareCommand
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
        ->setName('app:export:std')
        ->setDescription('Create JSON Data Files')
        ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to the repository'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        
        $path = $input->getArgument('path');
            
        if (substr($path, -1) === '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        
        $output->writeln("Exporting data in <info>$path</info>");
        
        $things = ['side', 'faction', 'type', 'cycle', 'pack'];
        
        foreach ($things as $thing) {
            $filepath = "${path}/${thing}s.json";
            $output->writeln("Exporting to <info>$filepath</info>");
            
            $command = $this->getApplication()->find('app:dump:std:base');
            $arguments = [ 'entityName' => $thing ];
            $subInput = new ArrayInput($arguments);
            $subOutput = new BufferedOutput();
            $returnCode = $command->run($subInput, $subOutput);
            
            if ($returnCode == 0) {
                $fs->dumpFile($filepath, $subOutput->fetch());
            } else {
                throw new \Exception("An error occured (code $returnCode)");
            }
        }
        
        $packs = $this->entityManager->getRepository('AppBundle:Pack')->findAll();

        /** @var Pack $pack */
        foreach ($packs as $pack) {
            $pack_code = $pack->getCode();
            $filepath = "${path}/pack/${pack_code}.json";
            $output->writeln("Exporting to <info>$filepath</info>");
    
            $command = $this->getApplication()->find('app:dump:std:cards');
            $arguments = [ 'pack_code' => $pack_code ];
            $subInput = new ArrayInput($arguments);
            $subOutput = new BufferedOutput();
            $returnCode = $command->run($subInput, $subOutput);
    
            if ($returnCode == 0) {
                $fs->dumpFile($filepath, $subOutput->fetch());
            } else {
                throw new \Exception("An error occured (code $returnCode)");
            }
        }
    }
}

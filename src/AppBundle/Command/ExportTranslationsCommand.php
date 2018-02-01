<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\BufferedOutput;

class ExportTranslationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('nrdb:export:trans')
        ->setDescription('Create Translation Files for every Locale')
        ->addOption(
                'locale',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "Locale to export"
        )
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
        
        $supported_locales = $this->getContainer()->getParameter('supported_locales');
        $default_locale = $this->getContainer()->getParameter('locale');
        $locales = $input->getOption('locale');
        if (empty($locales)) {
            $locales = $supported_locales;
        }
        
        if (count($locales) > 1) {
            // when multiple locales, the hint fallback = 0 doesn't work in translations:dump:cards
            //throw new \Exception("Sorry but multiple locales are not supported yet");
        }
        
        $path = $input->getArgument('path');
            
        if (substr($path, -1) === '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        
        $output->writeln("Exporting translations for <info>" . implode(",", $locales) . "</info> in <info>$path</info>");
        
        $things = ['side', 'faction', 'type', 'cycle', 'pack'];
        $packs = $this->getContainer()->get('doctrine')->getManager()->getRepository('AppBundle:Pack')->findAll();
        
        
        foreach ($locales as $locale) {
            if ($locale === $default_locale) {
                continue;
            }
            
            $command = $this->getApplication()->find('nrdb:translations:dump:things');
            foreach ($things as $thing) {
                $filepath = "${path}/translations/${locale}/${thing}s.{$locale}.json";
                $output->writeln("Exporting to <info>$filepath</info>");
                
                $arguments = [ 'entityName' => $thing, 'locale' => $locale ];
                $subInput = new ArrayInput($arguments);
                $subOutput = new BufferedOutput();
                $returnCode = $command->run($subInput, $subOutput);
                if ($returnCode == 0) {
                    $fs->dumpFile($filepath, $subOutput->fetch());
                } else {
                    throw new \Exception("An error occured (code $returnCode)");
                }
            }
        
            $command = $this->getApplication()->find('nrdb:translations:dump:cards');
            foreach ($packs as $pack) {
                $pack_code = $pack->getCode();
                $filepath = "${path}/translations/${locale}/pack/${pack_code}.{$locale}.json";
                $output->writeln("Exporting to <info>$filepath</info>");
        
                $arguments = [ 'pack_code' => $pack_code, 'locale' => $locale ];
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
}

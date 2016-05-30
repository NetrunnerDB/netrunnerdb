<?php

namespace Netrunnerdb\BuilderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\BufferedOutput;

class CreateJsonTranslationFilesCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('nrdb:translations:files')
		->setDescription('Create Translation Files for every Locale')
		->addOption(
				'path',
				'p',
				InputOption::VALUE_REQUIRED,
				"Path of the directory where to create the files"
		)
		->addArgument(
				'locales',
				InputArgument::IS_ARRAY,
				"Locales to export (separate multiple locales with a space)"
		)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$fs = new Filesystem();
		
		$locales = $input->getArgument('locales');
		$path = $input->getOption('path');

		if(!isset($path) || !$path) {
			$path = '.';
		}
		
		if(substr($path, -1) === '/') {
			$path = substr($path, 0, strlen($path) - 1); 
		}
		
		$output->writeln("Writing translation files for <info>" . implode(",", $locales) . "</info> in <info>$path</info>");
		
		$things = ['side', 'faction', 'type', 'cycle', 'pack'];
		
		foreach($things as $thing) {
			foreach($locales as $locale) {
				$filepath = "${path}/translations/${locale}/${thing}s.{$locale}.json";
				$output->writeln("Writing translation files for <info>$thing</info> in locale <info>$locale</info> to <info>$filepath</info>");
				
				$command = $this->getApplication()->find('nrdb:translations:dump:things');
				$arguments = [ 'entityName' => $thing, 'locale' => $locale ];
				$subInput = new ArrayInput($arguments);
				$subOutput = new BufferedOutput();
				$returnCode = $command->run($subInput, $subOutput);
				
				if($returnCode == 0) {
					$fs->dumpFile($filepath, $subOutput->fetch());
				} else {
					throw new \Exception("An error occured (code $returnCode)");
				}
			}
		}
		
		$packs = $this->getContainer()->get('doctrine')->getManager()->getRepository('NetrunnerdbCardsBundle:Pack')->findAll();
		
		foreach($packs as $pack) {
			$pack_code = $pack->getCode();
			foreach($locales as $locale) {
				$filepath = "${path}/translations/${locale}/pack/${pack_code}.{$locale}.json";
				$output->writeln("Writing translation files for Cards from <info>${pack_code}</info> in locale <info>$locale</info> to <info>$filepath</info>");
		
				$command = $this->getApplication()->find('nrdb:translations:dump:cards');
				$arguments = [ 'pack_code' => $pack_code, 'locale' => $locale ];
				$subInput = new ArrayInput($arguments);
				$subOutput = new BufferedOutput();
				$returnCode = $command->run($subInput, $subOutput);
		
				if($returnCode == 0) {
					$fs->dumpFile($filepath, $subOutput->fetch());
				} else {
					throw new \Exception("An error occured (code $returnCode)");
				}
			}
		}
	}
}
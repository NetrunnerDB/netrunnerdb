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

class DumpThingsTranslationsCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('nrdb:translations:dump:things')
		->setDescription('Dump Translations of Things for a Locale')
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
		$entityFullName = 'NetrunnerdbCardsBundle:'.ucfirst($entityName);
		
		/* @var $repository \Netrunnerdb\CardsBundle\Repository\TranslatableRepository */
		$repository = $this->getContainer()->get('doctrine')->getManager()->getRepository($entityFullName);
		
		$qb = $repository->setDefaultLocale($locale)->createQueryBuilder('e')->orderBy('e.code');
		
		$result = $repository->getResult($qb);
		
		$arr = [];
		
		foreach($result as $record) {
			$data = [
					"code" => $record->getCode(),
					"name" => $record->getName()
			];
			$arr[] = $data;
		}
		
		$output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ));
	}
}
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

class DumpCycleTranslationsCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('nrdb:dump:translations:cycles')
		->setDescription('Dump cycle translations for a locale')
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
		
		/* @var $repository \Netrunnerdb\CardsBundle\Repository\CycleRepository */
		$repository = $this->getContainer()->get('doctrine')->getManager()->getRepository('NetrunnerdbCardsBundle:Cycle');
		
		$qb = $repository->setDefaultLocale($locale)->createQueryBuilder('c')->orderBy('c.code');
		
		$cycles = $repository->getResult($qb);
		
		$output = [];
		
		foreach($cycles as $cycle) {
			/* @var $cycle \Netrunnerdb\CardsBundle\Entity\Cycle */
			$output[] = [
					"code" => $cycle->getCode(),
					"name" => $cycle->getName()
			];
		}
		
		echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	}
}
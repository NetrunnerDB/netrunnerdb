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

class DumpPackTranslationsCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('nrdb:dump:translations:packs')
		->setDescription('Dump pack translations for a locale')
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
		
		/* @var $repository \Netrunnerdb\CardsBundle\Repository\PackRepository */
		$repository = $this->getContainer()->get('doctrine')->getManager()->getRepository('NetrunnerdbCardsBundle:Pack');
		
		$qb = $repository->setDefaultLocale($locale)->createQueryBuilder('c')->orderBy('c.code');
		
		$packs = $repository->getResult($qb);
		
		$output = [];
		
		foreach($packs as $pack) {
			/* @var $pack \Netrunnerdb\CardsBundle\Entity\Pack */
			$output[] = [
					"code" => $pack->getCode(),
					"name" => $pack->getName()
			];
		}
		
		echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	}
}
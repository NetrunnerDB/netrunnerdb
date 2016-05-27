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

class DumpCardTranslationsCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
		->setName('nrdb:dump:translations:cards')
		->setDescription('Dump card translations for a locale')
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
		
		/* @var $repository \Netrunnerdb\CardsBundle\Repository\CardRepository */
		$repository = $this->getContainer()->get('doctrine')->getManager()->getRepository('NetrunnerdbCardsBundle:Card');
		
		$qb = $repository->setDefaultLocale($locale)->createQueryBuilder('c')->orderBy('c.code');
		
		$cards = $repository->getResult($qb);
		
		$output = [];
		
		foreach($cards as $card) {
			/* @var $card \Netrunnerdb\CardsBundle\Entity\Card */
			$output[] = [
					"code" => $card->getCode(),
					"title" => $card->getTitle(),
					"keywords" => $card->getKeywords(),
					"text" => $card->getText(),
					"flavor" => $card->getFlavor()
			];
		}
		
		echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	}
}
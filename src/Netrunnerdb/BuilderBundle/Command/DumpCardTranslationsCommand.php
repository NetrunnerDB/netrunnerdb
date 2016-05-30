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
		->setName('nrdb:translations:dump:cards')
		->setDescription('Dump Translations of Cards from a Pack for a Locale')
		->addArgument(
				'pack_code',
				InputArgument::REQUIRED,
				"Pack Code"
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
		$pack_code = $input->getArgument('pack_code');
		$locale = $input->getArgument('locale');
		
		$pack = $this->getContainer()->get('doctrine')->getManager()->getRepository('NetrunnerdbCardsBundle:Pack')->findOneBy(['code' => $pack_code]);
		
		if(!$pack) {
			throw new \Exception("Pack [$pack_code] cannot be found.");
		}
		
		/* @var $repository \Netrunnerdb\CardsBundle\Repository\CardRepository */
		$repository = $this->getContainer()->get('doctrine')->getManager()->getRepository('NetrunnerdbCardsBundle:Card');
		
		$qb = $repository->setDefaultLocale($locale)->createQueryBuilder('c')->where('c.pack = :pack')->setParameter('pack', $pack)->orderBy('c.code');
		
		$cards = $repository->getResult($qb);
		
		$arr = [];
		
		foreach($cards as $card) {
			$data = [
					"code" => $card->getCode(),
					"title" => $card->getTitle(),
			];
			if($keywords = $card->getKeywords()) {
				$data['keywords'] = $keywords;
			}
			if($text = $card->getText()) {
				$data['text'] = $text;
			}
			if($flavor = $card->getFlavor()) {
				$data['flavor'] = $flavor;
			}
			$arr[] = $data;
		}
		
		$output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ));
	}
}
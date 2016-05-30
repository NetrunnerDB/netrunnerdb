<?php

namespace Netrunnerdb\BuilderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
use Netrunnerdb\CardsBundle\Entity\Cycle;
use Netrunnerdb\CardsBundle\Entity\Pack;
use Netrunnerdb\CardsBundle\Entity\Card;

class ImportTranslationsCommand extends ContainerAwareCommand
{
	/* @var $em EntityManager */
	private $em;

	/* @var $output OutputInterface */
	private $output;
	
	private $locale;
	
	protected function configure()
	{
		$this
		->setName('nrdb:translations:import')
		->setDescription('Import cards data file in json format from a copy of https://github.com/zaroth/netrunner-cards-json')
		->addArgument(
				'locale',
				InputArgument::REQUIRED,
				'Locale to import'
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
		$this->em = $this->getContainer()->get('doctrine')->getEntityManager();
		$this->output = $output;
		
		$this->locale = $locale = $input->getArgument('locale');
		$path = $input->getArgument('path');

		if(substr($path, -1) === '/') {
			$path = substr($path, 0, strlen($path) - 1);
		}
		
		/* @var $helper \Symfony\Component\Console\Helper\QuestionHelper */
		$helper = $this->getHelper('question');
		
		$things = ['side', 'faction', 'type', 'cycle', 'pack'];
		
		foreach($things as $thing) {
			$fileInfo = $this->getFileInfo("${path}/translations/${locale}", "${thing}s.${locale}.json");
			$this->importThingsJsonFile($fileInfo, $thing);
		}
		
		$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
		if(!$helper->ask($input, $output, $question)) {
			die();
		}
		$this->em->flush();
		
		// third, cards
		
		/* only if cards.$locale.json exists (first version)
		$fileInfo = $this->getFileInfo("${path}/translations/${locale}", "cards.${locale}.json");
		$this->importCardsJsonFile($fileInfo);
		*/
		
		$fileSystemIterator = $this->getFileSystemIterator("${path}/translations/${locale}");
		
		foreach ($fileSystemIterator as $fileInfo) {
			$this->importCardsJsonFile($fileInfo);
		}
		
		$question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
		if(!$helper->ask($input, $output, $question)) {
			die();
		}
		$this->em->flush();
	}
	
	protected function importThingsJsonFile(\SplFileInfo $fileinfo, $thing)
	{
		$list = $this->getDataFromFile($fileinfo);
		foreach($list as $data)
		{
			$entity = $this->getEntityFromData('Netrunnerdb\\CardsBundle\\Entity\\'.ucfirst($thing), $data, [
					'code',
					'name'
			], []);
		
			$this->em->persist($entity);
		}
	}

	protected function importCardsJsonFile(\SplFileInfo $fileinfo)
	{
		$cardsData = $this->getDataFromFile($fileinfo);
		foreach($cardsData as $cardData) {
			$card = $this->getEntityFromData('Netrunnerdb\CardsBundle\Entity\Card', $cardData, [
					'code',
					'title'
			], [
					'flavor',
					'keywords',
					'text'
			]);
			
			$this->em->persist($card);
		}
	}

	protected function copyFieldValueToEntity($entity, $entityName, $fieldName, $newJsonValue)
	{
		$metadata = $this->em->getClassMetadata($entityName);
		$type = $metadata->fieldMappings[$fieldName]['type'];
	
		// new value, by default what json gave us is the correct typed value
		$newTypedValue = $newJsonValue;
	
		// current value, by default the json, serialized value is the same as what's in the entity
		$getter = 'get'.ucfirst($fieldName);
		$currentJsonValue = $currentTypedValue = $entity->$getter();
	
		// if the field is a data, the default assumptions above are wrong
		if(in_array($type, ['date', 'datetime'])) {
			if($newJsonValue !== null) {
				$newTypedValue = new \DateTime($newJsonValue);
			}
			if($currentTypedValue !== null) {
				switch($type) {
					case 'date': {
						$currentJsonValue = $currentTypedValue->format('Y-m-d');
						break;
					}
					case 'datetime': {
						$currentJsonValue = $currentTypedValue->format('Y-m-d H:i:s');
					}
				}
			}
		}
	
		$different = ($currentJsonValue !== $newJsonValue);
		if($different) {
			$this->output->writeln("Changing the <info>$fieldName</info> of <info>".$entity->toString()."</info>");
			$this->output->writeln("    from: ".$currentJsonValue);
			$this->output->writeln("     to : ".$newJsonValue);
				
			$setter = 'set'.ucfirst($fieldName);
			$entity->$setter($newTypedValue);
		}
	}
	
	protected function copyKeyToEntity($entity, $entityName, $data, $key, $isMandatory = TRUE)
	{
		$metadata = $this->em->getClassMetadata($entityName);
	
		if(!key_exists($key, $data)) {
			if($isMandatory) {
				throw new \Exception("Missing key [$key] in ".json_encode($data));
			} else {
				$data[$key] = null;
			}
		}
		$value = $data[$key];
	
		if(!key_exists($key, $metadata->fieldNames)) {
			throw new \Exception("Invalid key [$key] in ".json_encode($data));
		}
		$fieldName = $metadata->fieldNames[$key];
	
		$this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value);
	}
	
	protected function getEntityFromData($entityName, $data, $mandatoryKeys, $optionalKeys)
	{
		if(!key_exists('code', $data)) {
			throw new \Exception("Missing key [code] in ".json_encode($data));
		}
	
		$entity = $this->em->getRepository($entityName)->findOneBy(['code' => $data['code']]);
		if(!$entity) {
			throw new \Exception("Cannot find entity [code]");
		}
		$entity->setTranslatableLocale($this->locale);
		$this->em->refresh($entity);
		$entity->setTranslatableLocale($this->locale);
		
		foreach($mandatoryKeys as $key) {
			$this->copyKeyToEntity($entity, $entityName, $data, $key, TRUE);
		}

		foreach($optionalKeys as $key) {
			$this->copyKeyToEntity($entity, $entityName, $data, $key, FALSE);
		}
		
		return $entity;
	}
	
	protected function getDataFromFile(\SplFileInfo $fileinfo)
	{
	
		$file = $fileinfo->openFile('r');
		$file->setFlags(\SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
	
		$lines = [];
		foreach($file as $line) {
			if($line !== false) $lines[] = $line;
		}
		$content = implode('', $lines);
	
		$data = json_decode($content, true);
	
		if($data === null) {
			throw new \Exception("File [".$fileinfo->getPathname()."] contains incorrect JSON (error code ".json_last_error().")");
		}
	
		return $data;
	}
	
	protected function getFileInfo($path, $filename)
	{
		$fs = new Filesystem();
		
		if(!$fs->exists($path)) {
			throw new \Exception("No repository found at [$path]");
		}
		
		$filepath = "$path/$filename";
		
		if(!$fs->exists($filepath)) {
			throw new \Exception("No $filename file found at [$path]");
		}
		
		return new \SplFileInfo($filepath);
	}
	
	protected function getFileSystemIterator($path)
	{
		$fs = new Filesystem();
		
		if(!$fs->exists($path)) {
			throw new \Exception("No repository found at [$path]");
		}
		
		$directory = 'pack';
		
		if(!$fs->exists("$path/$directory")) {
			throw new \Exception("No '$directory' directory found at [$path]");
		}
		
		$iterator = new \GlobIterator("$path/$directory/*.json");
		
		if(!$iterator->count()) {
			throw new \Exception("No json file found at [$path/set]");
		}
		
		return $iterator;
	}
}
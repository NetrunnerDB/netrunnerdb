<?php

namespace AppBundle\Command;

use AppBundle\Behavior\Entity\AbstractTranslatableEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportTransCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var OutputInterface $output */
    private $output;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
        ->setName('app:import:trans')
        ->setDescription('Import translation data in json format from a copy of https://github.com/zaroth/netrunner-cards-json')
        ->addOption(
                'locale',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "Locale to import"
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
        $this->output = $output;
        
        $supported_locales = $this->getContainer()->getParameter('supported_locales');
        $default_locale = $this->getContainer()->getParameter('locale');
        $locales = $input->getOption('locale');
        if (empty($locales)) {
            $locales = $supported_locales;
        }
                
        $path = $input->getArgument('path');

        if (substr($path, -1) === '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        
        $things = ['side', 'faction', 'type', 'cycle', 'pack'];
        
        foreach ($locales as $locale) {
            if ($locale === $default_locale) {
                continue;
            }
            $output->writeln("Importing translations for <info>${locale}</info>");
            foreach ($things as $thing) {
                $output->writeln("Importing translations for <info>${thing}s</info> in <info>${locale}</info>");
                $fileInfo = $this->getFileInfo("${path}/translations/${locale}", "${thing}s.${locale}.json");
                $this->importThingsJsonFile($fileInfo, $locale, $thing);
            }
            $this->entityManager->flush();
            
            $fileSystemIterator = $this->getFileSystemIterator("${path}/translations/${locale}");
            
            $output->writeln("Importing translations for <info>cards</info> in <info>${locale}</info>");
            foreach ($fileSystemIterator as $fileInfo) {
                $output->writeln("Importing translations for <info>cards</info> from <info>".$fileInfo->getFilename()."</info>");
                $this->importCardsJsonFile($fileInfo, $locale);
            }
            
            $this->entityManager->flush();
        }
    }
    
    protected function importThingsJsonFile(\SplFileInfo $fileinfo, string $locale, string $thing)
    {
        $list = $this->getDataFromFile($fileinfo);
        foreach ($list as $data) {
            $this->updateEntityFromData($locale, 'AppBundle\\Entity\\'.ucfirst($thing), $data, [
                    'code',
                    'name'
            ], []);
        }
    }

    protected function importCardsJsonFile(\SplFileInfo $fileinfo, string $locale)
    {
        $cardsData = $this->getDataFromFile($fileinfo);
        
        $progress = new ProgressBar($this->output, count($cardsData));
        $progress->start();
                
        foreach ($cardsData as $cardData) {
            $progress->advance();
            
            $this->updateEntityFromData($locale, 'AppBundle\Entity\Card', $cardData, [
                    'code',
                    'title'
            ], [
                    'flavor',
                    'keywords',
                    'text'
            ]);
        }
        
        $progress->finish();
        $progress->clear();
        $this->output->write("\n");
    }

    protected function copyFieldValueToEntity($entity, string $entityName, string $fieldName, $newJsonValue)
    {
        $metadata = $this->entityManager->getClassMetadata($entityName);
        $type = $metadata->fieldMappings[$fieldName]['type'];
    
        // new value, by default what json gave us is the correct typed value
        $newTypedValue = $newJsonValue;
    
        // current value, by default the json, serialized value is the same as what's in the entity
        $getter = 'get'.ucfirst($fieldName);
        $currentJsonValue = $currentTypedValue = $entity->$getter();
    
        // if the field is a data, the default assumptions above are wrong
        if (in_array($type, ['date', 'datetime'])) {
            if ($newJsonValue !== null) {
                $newTypedValue = new \DateTime($newJsonValue);
            }
            if ($currentTypedValue instanceof \DateTime) {
                switch ($type) {
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
        if ($different) {
            $setter = 'set'.ucfirst($fieldName);
            $entity->$setter($newTypedValue);
        }
    }
    
    protected function copyKeyToEntity($entity, string $entityName, array $data, string $key, bool $isMandatory = true)
    {
        $metadata = $this->entityManager->getClassMetadata($entityName);
    
        if (!key_exists($key, $data)) {
            if ($isMandatory) {
                throw new \Exception("Missing key [$key] in ".json_encode($data));
            } else {
                $data[$key] = null;
            }
        }
        $value = $data[$key];
    
        if (!key_exists($key, $metadata->fieldNames)) {
            throw new \Exception("Invalid key [$key] in ".json_encode($data));
        }
        $fieldName = $metadata->fieldNames[$key];
    
        $this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value);
    }
    
    protected function updateEntityFromData(string $locale, string $entityName, array $data, array $mandatoryKeys, array $optionalKeys)
    {
        if (!key_exists('code', $data)) {
            throw new \Exception("Missing key [code] in ".json_encode($data));
        }
    
        # skip empty translations
        if (!isset($data['title']) && !isset($data['name'])) {
            return;
        }
        
        $entity = $this->entityManager->getRepository($entityName)->findOneBy(['code' => $data['code']]);
        if (!$entity instanceof AbstractTranslatableEntity) {
            throw new \Exception("Cannot find entity $entityName code [".$data['code']."]");
        }
        $entity->setTranslatableLocale($locale);
        $this->entityManager->refresh($entity);
        
        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, true);
        }

        foreach ($optionalKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, false);
        }
    }
    
    protected function getDataFromFile(\SplFileInfo $fileinfo)
    {
        $file = $fileinfo->openFile('r');
        $file->setFlags(\SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
    
        $lines = [];
        foreach ($file as $line) {
            if ($line !== false) {
                $lines[] = $line;
            }
        }
        $content = implode('', $lines);
    
        $data = json_decode($content, true);
    
        if ($data === null) {
            throw new \Exception("File [".$fileinfo->getPathname()."] contains incorrect JSON (error code ".json_last_error().")");
        }
    
        return $data;
    }
    
    /**
     * @return \SplFileInfo
     */
    protected function getFileInfo(string $path, string $filename)
    {
        $fs = new Filesystem();
        
        if (!$fs->exists($path)) {
            throw new \Exception("No repository found at [$path]");
        }
        
        $filepath = "$path/$filename";
        
        if (!$fs->exists($filepath)) {
            throw new \Exception("No $filename file found at [$path]");
        }
        
        return new \SplFileInfo($filepath);
    }

    /**
     * @param string $path
     * @return \GlobIterator
     * @throws \Exception
     */
    protected function getFileSystemIterator(string $path)
    {
        $fs = new Filesystem();
        
        if (!$fs->exists($path)) {
            throw new \Exception("No repository found at [$path]");
        }
        
        $directory = 'pack';
        
        if (!$fs->exists("$path/$directory")) {
            throw new \Exception("No '$directory' directory found at [$path]");
        }
        
        $iterator = new \GlobIterator("$path/$directory/*.json");
        
        if (!$iterator->count()) {
            throw new \Exception("No json file found at [$path/set]");
        }
        
        return $iterator;
    }
}

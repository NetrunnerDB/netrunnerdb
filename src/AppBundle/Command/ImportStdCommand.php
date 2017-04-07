<?php

namespace AppBundle\Command;

use AppBundle\Entity\Card;
use AppBundle\Entity\Mwlslot;
use AppBundle\Entity\Prebuiltslot;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use GlobIterator;
use SplFileInfo;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class ImportStdCommand extends ContainerAwareCommand
{
    /* @var $em EntityManager */

    private $em;

    /* @var $output OutputInterface */
    private $output;
    private $collections = [];

    protected function configure ()
    {
        $this
                ->setName('nrdb:import:std')
                ->setDescription('Import std data file in json format from a copy of https://github.com/zaroth/netrunner-cards-json')
                ->addArgument('path', InputArgument::REQUIRED, 'Path to the repository')
                ->addOption('force', 'f', InputOption::VALUE_NONE, "Yes to all questions")
        ;
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $force = $input->getOption('force');
        
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
        $this->output = $output;

        /* @var $helper QuestionHelper */
        $helper = $this->getHelper('question');

        // sides

        $output->writeln("Importing Sides...");
        $sidesFileInfo = $this->getFileInfo($path, 'sides.json');
        $imported = $this->importSidesJsonFile($sidesFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Side');
        $output->writeln("Done.");

        // factions 

        $output->writeln("Importing Factions...");
        $factionsFileInfo = $this->getFileInfo($path, 'factions.json');
        $imported = $this->importFactionsJsonFile($factionsFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Faction');
        $output->writeln("Done.");

        // types

        $output->writeln("Importing Types...");
        $typesFileInfo = $this->getFileInfo($path, 'types.json');
        $imported = $this->importTypesJsonFile($typesFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Type');
        $output->writeln("Done.");

        // cycles

        $output->writeln("Importing Cycles...");
        $cyclesFileInfo = $this->getFileInfo($path, 'cycles.json');
        $imported = $this->importCyclesJsonFile($cyclesFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Cycle');
        $output->writeln("Done.");

        // packs

        $output->writeln("Importing Packs...");
        $packsFileInfo = $this->getFileInfo($path, 'packs.json');
        $imported = $this->importPacksJsonFile($packsFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Pack');
        $output->writeln("Done.");

        // cards

        $output->writeln("Importing Cards...");
        $fileSystemIterator = $this->getFileSystemIterator($path);
        $imported = [];
        foreach($fileSystemIterator as $fileinfo) {
            $imported = array_merge($imported, $this->importCardsJsonFile($fileinfo));
        }
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $output->writeln("Done.");

        // prebuilt

        $output->writeln("Importing Prebuilts...");
        $prebuiltFileInfo = $this->getFileInfo($path, 'prebuilts.json');
        $imported = $this->importPrebuiltJsonFile($prebuiltFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $output->writeln("Done.");

        // mwl

        $output->writeln("Importing MWL...");
        $mwlFileInfo = $this->getFileInfo($path, 'mwl.json');
        $imported = $this->importMwlJsonFile($mwlFileInfo);
        if(!$force && count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if(!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $output->writeln("Done.");
    }

    protected function importSidesJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach($list as $data) {
            $side = $this->getEntityFromData('AppBundle\\Entity\\Side', $data, [
                'code',
                'name'
                    ], [], []);
            if($side) {
                $result[] = $side;
                $this->em->persist($side);
            }
        }

        return $result;
    }

    protected function importFactionsJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach($list as $data) {
            $faction = $this->getEntityFromData('AppBundle\\Entity\\Faction', $data, [
                'code',
                'name',
                'color',
                'is_mini'
                    ], [
                'side_code'
                    ], []);
            if($faction) {
                $result[] = $faction;
                $this->em->persist($faction);
            }
        }

        return $result;
    }

    protected function importTypesJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach($list as $data) {
            $type = $this->getEntityFromData('AppBundle\\Entity\\Type', $data, [
                'code',
                'name',
                'position',
                'is_subtype'
                    ], [
                'side_code'
                    ], []);
            if($type) {
                $result[] = $type;
                $this->em->persist($type);
            }
        }

        return $result;
    }

    protected function importCyclesJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $cyclesData = $this->getDataFromFile($fileinfo);
        foreach($cyclesData as $cycleData) {
            $cycle = $this->getEntityFromData('AppBundle\Entity\Cycle', $cycleData, [
                'code',
                'name',
                'position',
                'size',
                'rotated'
                    ], [], []);
            if($cycle) {
                $result[] = $cycle;
                $this->em->persist($cycle);
            }
        }

        return $result;
    }

    protected function importPacksJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $packsData = $this->getDataFromFile($fileinfo);
        foreach($packsData as $packData) {
            $pack = $this->getEntityFromData('AppBundle\Entity\Pack', $packData, [
                'code',
                'name',
                'position',
                'size',
                'date_release'
                    ], [
                'cycle_code'
                    ], []);
            if($pack) {
                $result[] = $pack;
                $this->em->persist($pack);
            }
        }

        return $result;
    }

    protected function importCardsJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $code = $fileinfo->getBasename('.json');

        $pack = $this->em->getRepository('AppBundle:Pack')->findOneBy(['code' => $code]);
        if(!$pack)
            throw new Exception("Unable to find Pack [$code]");

        $cardsData = $this->getDataFromFile($fileinfo);
        foreach($cardsData as $cardData) {
            $card = $this->getEntityFromData('AppBundle\Entity\Card', $cardData, [
                'code',
                'deck_limit',
                'position',
                'quantity',
                'title',
                'uniqueness'
                    ], [
                'faction_code',
                'pack_code',
                'side_code',
                'type_code'
                    ], [
                'illustrator',
                'flavor',
                'keywords',
                'text',
                'cost',
                'faction_cost',
                'trash_cost'
            ]);
            if($card) {
                $result[] = $card;
                $this->em->persist($card);
            }
        }

        return $result;
    }

    protected function importPrebuiltJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $prebuiltData = $this->getDataFromFile($fileinfo);
        foreach($prebuiltData as $prebuiltData) {
            $prebuilt = $this->getEntityFromData('AppBundle\Entity\Prebuilt', $prebuiltData, [
                'code',
                'name',
                'date_release',
                'position'
                    ], [], []);
            if($prebuilt) {
                $result[] = $prebuilt;
                $this->em->persist($prebuilt);

                foreach($prebuiltData['cards'] as $card_code => $quantity) {
                    $card = $this->em->getRepository('AppBundle:Card')->findOneBy(['code' => $card_code]);
                    if(!$card)
                        continue;
                    $prebuiltslot = new Prebuiltslot();
                    $prebuiltslot->setCard($card);
                    $prebuiltslot->setQuantity($quantity);
                    $prebuiltslot->setPrebuilt($prebuilt);
                    $this->em->persist($prebuiltslot);

                    if($card->getType()->getCode() === 'identity') {
                        $prebuilt->setIdentity($card);
                        $prebuilt->setFaction($card->getFaction());
                        $prebuilt->setSide($card->getFaction()->getSide());
                    }
                }
            }
        }

        return $result;
    }

    protected function importMwlJsonFile (SplFileInfo $fileinfo)
    {
        $result = [];

        $mwlData = $this->getDataFromFile($fileinfo);
        foreach($mwlData as $mwlData) {
            $mwl = $this->getEntityFromData('AppBundle\Entity\Mwl', $mwlData, [
                'code',
                'name',
                'date_start'
                    ], [], []);
            if($mwl) {
                $result[] = $mwl;
                $this->em->persist($mwl);

                foreach($mwlData['cards'] as $card_code => $penalty) {
                    $card = $this->em->getRepository('AppBundle:Card')->findOneBy(['code' => $card_code]);
                    if(!$card)
                        continue;
                    $mwlslot = new Mwlslot();
                    $mwlslot->setCard($card);
                    $mwlslot->setPenalty($penalty);
                    $mwlslot->setMwl($mwl);
                    $this->em->persist($mwlslot);
                }
            }
        }

        return $result;
    }

    protected function copyFieldValueToEntity ($entity, $entityName, $fieldName, $newJsonValue)
    {
        $metadata = $this->em->getClassMetadata($entityName);
        $type = $metadata->fieldMappings[$fieldName]['type'];

        // new value, by default what json gave us is the correct typed value
        $newTypedValue = $newJsonValue;

        // current value, by default the json, serialized value is the same as what's in the entity
        $getter = 'get' . ucfirst($fieldName);
        $currentJsonValue = $currentTypedValue = $entity->$getter();

        // if the field is a data, the default assumptions above are wrong
        if(in_array($type, ['date', 'datetime'])) {
            if($newJsonValue !== null) {
                $newTypedValue = new DateTime($newJsonValue);
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
            $this->output->writeln("Changing the <info>$fieldName</info> of <info>" . $entity->toString() . "</info>");
            $this->output->writeln("    from: " . $currentJsonValue);
            $this->output->writeln("     to : " . $newJsonValue);

            $setter = 'set' . ucfirst($fieldName);
            $entity->$setter($newTypedValue);
        }
    }

    protected function copyKeyToEntity ($entity, $entityName, $data, $key, $isMandatory = TRUE)
    {
        $metadata = $this->em->getClassMetadata($entityName);

        if(!key_exists($key, $data)) {
            if($isMandatory) {
                throw new Exception("Missing key [$key] in " . json_encode($data));
            } else {
                $data[$key] = null;
            }
        }
        $value = $data[$key];

        if(!key_exists($key, $metadata->fieldNames)) {
            throw new Exception("Invalid key [$key] in " . json_encode($data));
        }
        $fieldName = $metadata->fieldNames[$key];

        $this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value);
    }

    /**
     * 
     * @param string $entityName
     * @param array $data
     * @param array $mandatoryKeys
     * @param array $foreignKeys
     * @param array $optionalKeys
     * @throws Exception
     * @return object
     */
    protected function getEntityFromData ($entityName, $data, $mandatoryKeys, $foreignKeys, $optionalKeys)
    {
        if(!key_exists('code', $data)) {
            throw new Exception("Missing key [code] in " . json_encode($data));
        }

        $entity = $this->em->getRepository($entityName)->findOneBy(['code' => $data['code']]);
        if(!$entity) {
            $entity = new $entityName();
        }
        $orig = $entity->serialize();

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, TRUE);
        }

        foreach($optionalKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, FALSE);
        }

        foreach($foreignKeys as $key) {
            $foreignEntityShortName = ucfirst(str_replace('_code', '', $key));

            if(!key_exists($key, $data)) {
                throw new Exception("Missing key [$key] in " . json_encode($data));
            }

            $foreignCode = $data[$key];
            if($foreignCode === null) {
                continue;
            }
            if(!key_exists($foreignEntityShortName, $this->collections)) {
                throw new Exception("No collection for [$foreignEntityShortName] in " . json_encode($data));
            }
            if(!key_exists($foreignCode, $this->collections[$foreignEntityShortName])) {
                throw new Exception("Invalid code [$foreignCode] for key [$key] in " . json_encode($data));
            }
            $foreignEntity = $this->collections[$foreignEntityShortName][$foreignCode];

            $getter = 'get' . $foreignEntityShortName;
            if(!$entity->$getter() || $entity->$getter()->getId() !== $foreignEntity->getId()) {
                $this->output->writeln("Changing the <info>$key</info> of <info>" . $entity->toString() . "</info>");
                $setter = 'set' . $foreignEntityShortName;
                $entity->$setter($foreignEntity);
            }
        }

        // special case for Card
        if($entityName === 'AppBundle\Entity\Card') {
            // calling a function whose name depends on the type_code
            $functionName = 'import' . $entity->getType()->getName() . 'Data';
            $this->$functionName($entity, $data);
        }

        $newer = $entity->serialize();

        // special case for Mwl
        if($entityName === 'AppBundle\Entity\Mwl') {
            $newer['cards'] = $data['cards'];
            unset($newer['active']);
            unset($orig['active']);
        }

        if(!$this->deepArrayEquality($newer, $orig))
            return $entity;
    }

    /**
     * Encodes an array in JSON in such a way that two identical arrays have the same representation
     * @param array $data
     * @return string
     */
    protected function uniquelyEncodeJson ($data)
    {
        ksort($data);
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Performs a deep equality check of two arrays
     * @param unknown $array1
     * @param unknown $array2
     * @return boolean
     */
    protected function deepArrayEquality ($array1, $array2)
    {
        return $this->uniquelyEncodeJson($array1) === $this->uniquelyEncodeJson($array2);
    }

    protected function importAgendaData (Card $card, $data)
    {
        $mandatoryKeys = [
            'advancement_cost',
            'agenda_points'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importAssetData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost',
            'trash_cost'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importEventData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importHardwareData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importICEData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost',
            'strength'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importIdentityData (Card $card, $data)
    {
        $mandatoryKeys = [
            'minimum_deck_size'
        ];

        if($card->getPack()->getCode() !== 'draft') {
            $mandatoryKeys[] = 'influence_limit';
        }

        if($card->getSide()->getCode() === 'runner') {
            $mandatoryKeys[] = 'base_link';
        }

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importOperationData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importProgramData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'memory_cost',
            'faction_cost'
        ];

        if(strstr($card->getKeywords(), 'Icebreaker') !== FALSE) {
            $mandatoryKeys[] = 'strength';
        }

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importResourceData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function importUpgradeData (Card $card, $data)
    {
        $mandatoryKeys = [
            'cost',
            'faction_cost',
            'trash_cost'
        ];

        foreach($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, TRUE);
        }
    }

    protected function getDataFromFile (SplFileInfo $fileinfo)
    {

        $file = $fileinfo->openFile('r');
        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $lines = [];
        foreach($file as $line) {
            if($line !== false)
                $lines[] = $line;
        }
        $content = implode('', $lines);

        $data = json_decode($content, true);

        if($data === null) {
            throw new Exception("File [" . $fileinfo->getPathname() . "] contains incorrect JSON (error code " . json_last_error() . ")");
        }

        return $data;
    }

    protected function getFileInfo ($path, $filename)
    {
        $fs = new Filesystem();

        if(!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $filepath = "$path/$filename";

        if(!$fs->exists($filepath)) {
            throw new Exception("No $filename file found at [$path]");
        }

        return new SplFileInfo($filepath);
    }

    protected function getFileSystemIterator ($path)
    {
        $fs = new Filesystem();

        if(!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $directory = 'pack';

        if(!$fs->exists("$path/$directory")) {
            throw new Exception("No '$directory' directory found at [$path]");
        }

        $iterator = new GlobIterator("$path/$directory/*.json");

        if(!$iterator->count()) {
            throw new Exception("No json file found at [$path/set]");
        }

        return $iterator;
    }

    protected function loadCollection ($entityShortName)
    {
        $this->collections[$entityShortName] = [];

        $entities = $this->em->getRepository('AppBundle:' . $entityShortName)->findAll();

        foreach($entities as $entity) {
            $this->collections[$entityShortName][$entity->getCode()] = $entity;
        }
    }

}

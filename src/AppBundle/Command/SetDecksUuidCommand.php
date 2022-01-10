<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Update UUIDs for any decks that are missing them. 
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class SetDecksUuidCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
                ->setName('app:uuid:decks')
                ->setDescription('Set a UUID for any deck missing it.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Construct a v4 UUID in SQL according to https://www.ietf.org/rfc/rfc4122.txt
        $sql = "UPDATE deck "
            . "SET uuid = "
            . "LOWER(CONCAT("
            // start with random bits
            . "HEX(RANDOM_BYTES(4)), '-', "
            . "HEX(RANDOM_BYTES(2)), '-"
            // v4 has 0100 as the version indicator in bits 6 and 7 of the time_hi_and_version field, so always a 4 in hex.
            . "4', "
            // padded out by random bits.
            . "SUBSTR(HEX(RANDOM_BYTES(2)), 2, 3), '-', "
            . "CONCAT("
            // The spec says: Set bits 6 and 7 of the clock_seq_hi_and_reserved value to 10.
            // which means this next hex value is always one of 8,9,a,or b, which this next bit does in an ugly way.
            . "HEX(FLOOR(ASCII(RANDOM_BYTES(1)) / 64)+8), "
            // padded out by random bits.
            . "SUBSTR(HEX(RANDOM_BYTES(2)), 2, 3)), "
            // Close it out with more random bits.
            . "'-', HEX(RANDOM_BYTES(6))"
            . "))" // LOWER(CONCAT(
            // Only for decks missing a UUID already.
            . " WHERE uuid IS NULL";

        $this->entityManager->getConnection()->executeQuery($sql);

        $output->writeln("<info>Done</info>");
    }
}

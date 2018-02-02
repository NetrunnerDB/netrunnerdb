<?php


namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class DiffService
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function diffContents(array $decks)
    {

        // n flat lists of the cards of each decklist
        $ensembles = [];
        foreach ($decks as $deck) {
            $cards = [];
            foreach ($deck as $code => $qty) {
                for ($i = 0; $i < $qty; $i++) {
                    $cards[] = $code;
                }
            }
            $ensembles[] = $cards;
        }

        // 1 flat list of the cards seen in every decklist
        $conjunction = [];
        for ($i = 0; $i < count($ensembles[0]); $i++) {
            $code = $ensembles[0][$i];
            $indexes = [$i];
            for ($j = 1; $j < count($ensembles); $j++) {
                $index = array_search($code, $ensembles[$j]);
                if ($index !== false) {
                    $indexes[] = $index;
                } else {
                    break;
                }
            }
            if (count($indexes) === count($ensembles)) {
                $conjunction[] = $code;
                for ($j = 0; $j < count($indexes); $j++) {
                    $list = $ensembles[$j];
                    array_splice($list, $indexes[$j], 1);
                    $ensembles[$j] = $list;
                }
                $i--;
            }
        }

        $listings = [];
        for ($i = 0; $i < count($ensembles); $i++) {
            $listings[$i] = array_count_values($ensembles[$i]);
        }
        $intersect = array_count_values($conjunction);

        return [$listings, $intersect];
    }
}

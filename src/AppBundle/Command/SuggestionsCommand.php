<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Entity\Side;

class SuggestionsCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var string $publicDir */
    private $publicDir;

    public function __construct(string $name = null, EntityManagerInterface $entityManager, string $publicDir)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->publicDir = $publicDir;
    }

    protected function configure()
    {
        $this
        ->setName('app:suggestions')
        ->setDescription('Compute and save the suggestions matrix')
        ->addArgument(
                'side',
                InputArgument::REQUIRED,
                'Which side'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '1G');
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        
        $side_code = $input->getArgument('side');
        $side = $this->entityManager->getRepository('AppBundle:Side')->findOneBy(['code' => $side_code]);
        if (!$side instanceof Side) {
            throw new \Exception("Side not found [$side_code]");
        }
        $data = $this->getSuggestions($side);
        file_put_contents($this->publicDir . "/$side_code.json", json_encode($data));
        $output->writeln('done');
    }

    private function addToMatrix(array &$matrix, array $cardIndexById, int $card_id_1, int $card_id_2)
    {
        $card_index_1 = $cardIndexById[$card_id_1];
        $card_index_2 = $cardIndexById[$card_id_2];
        if (!isset($matrix[$card_index_1])) {
            $matrix[$card_index_1] = [];
        }
        if (!isset($matrix[$card_index_1][$card_index_2])) {
            $matrix[$card_index_1][$card_index_2] = 0;
        }
        $matrix[$card_index_1][$card_index_2] += 1;
    }
    
    private function fillMatrix(array &$matrix, array $cardIndexById, int $side_id)
    {
        $dbh = $this->entityManager->getConnection();
        
        // a numeric array of all the decklists
        $decklists = $dbh->executeQuery(
                "SELECT
				d.id
                from deck d
                where d.side_id=?
                order by d.id",
            [ $side_id ]
        )->fetchAll();
        
        // for each decklist, will give the cards included in this decklist
        $stmt = $dbh->prepare(
                "SELECT
				d.card_id
                from deckslot d
                where d.deck_id=?
                order by d.card_id"
        );
        
        foreach ($decklists as $decklist_id) {
            $stmt->execute([$decklist_id['id']]);
            // numeric array of card ids found in the decklist
            $slots = $stmt->fetchAll();
        
            // for each pair of card id found in $slots, we add 1 in the correct spot in $matrix
            for ($i=0; $i<count($slots); $i++) {
                for ($j=$i+1; $j<count($slots); $j++) {
                    $this->addToMatrix($matrix, $cardIndexById, $slots[$j]['card_id'], $slots[$i]['card_id']);
                }
            }
        }
    }
    
    private function normalizeMatrix(array &$matrix, array $cardsByIndex)
    {
        /*
    	 * now we have to weight the cards. The numbers in $matrix are the number of decklists
    	 * that include both x and y cards, so they are relative to the commonness of both
    	 * cards.
    	 * if an uncommon card A is often paired with an uncommon card B and a common card C,
    	 * what result do we want when the user picks A ?
    	 * -> we want to suggest B more than C
    	 * so we want $matrix(A,B) > $matrix(A,C)
    	 * so we want $divider(A,B) < $divider(A,C)
    	 */
        
        for ($i=0; $i<count($matrix); $i++) {
            for ($j=0; $j<$i; $j++) {
                //$divider = min($cardsByIndex[$i]['nbdecklists'], $cardsByIndex[$j]['nbdecklists']);
                //$divider = $cardsByIndex[$i]['nbdecklists'] + $cardsByIndex[$j]['nbdecklists'];
                //$divider = max($cardsByIndex[$i]['nbdecklists'], $cardsByIndex[$j]['nbdecklists']);
                //$divider = $cardsByIndex[$i]['faction_id'] == $cardsByIndex[$j]['faction_id'] ? 1000 : 2000;
                //$divider = max(100, min($cardsByIndex[$i]['nbdecklists'], $cardsByIndex[$j]['nbdecklists']));
                $divider = sqrt(min($cardsByIndex[$i]['nbdecklists'], $cardsByIndex[$j]['nbdecklists']));
                $matrix[$i][$j] = round($matrix[$i][$j] / $divider * 100);
            }
        }
    }
    
    private function getCardsByIndex(int $side_id)
    {
        $dbh = $this->entityManager->getConnection();
        
        return $dbh->executeQuery(
                "SELECT
				c.id,
                c.code,
                count(*) nbdecklists
                from card c
                join deckslot d on d.card_id=c.id
                where c.side_id=?
                group by c.id, c.code, c.faction_id
                order by c.id",
        
            [ $side_id ]
        
        )->fetchAll();
    }
    
    /**
     * returns a matrix where each point x,y is
     * the probability that the cards x and y
     * are seen together in a decklist
     * also returns an array of card codes
     * x and y are private indexes, not card.id
     */
    private function getSuggestions(Side $side)
    {
        $side_id = $side->getId();
        
        // a numeric array giving all cards with the number of decklists they appear on
        $cardsByIndex = $this->getCardsByIndex($side_id);
       
        // an associative array giving for each card id, its position in $cardsByIndex (card index)
        $cardIndexById = array_flip(array_map(function ($card) {
            return intval($card['id']);
        }, $cardsByIndex));
        
        // an associative array giving for each card index, its code
        $cardCodesByIndex = array_map(function ($card) {
            return $card['code'];
        }, $cardsByIndex);
        
        // a numeric array of numeric arrays giving for each couple of card indexes, how many decklists have both cards
        $matrix = [];
        
        // initializing that matrix with zeros
        foreach ($cardsByIndex as $index => $card) {
            $matrix[$index] = $index ? array_fill(0, $index, 0) : [];
        }
        
        $this->fillMatrix($matrix, $cardIndexById, $side_id);
        $this->normalizeMatrix($matrix, $cardsByIndex);
        
        return [
                "index" => $cardCodesByIndex,
                "matrix" => $matrix
        ];
    }
}

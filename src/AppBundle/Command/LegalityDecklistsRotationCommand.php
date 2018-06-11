<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Service\RotationService;

/**
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class LegalityDecklistsRotationCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    
    /** @var RotationService $rotationService */
    private $rotationService;

    public function __construct(EntityManagerInterface $entityManager, RotationService $rotationService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->rotationService = $rotationService;
    }

    protected function configure()
    {
        $this
                ->setName('app:legality:decklists-rotation')
                ->setDescription('Compute decklist legality regarding rotations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = "UPDATE decklist d SET d.is_legal=0 WHERE d.is_legal=1"
                . " AND EXISTS(SELECT *"
                . " FROM decklistslot s"
                . " JOIN card c ON c.id=s.card_id"
                . " JOIN pack p ON p.id=c.pack_id"
                . " JOIN cycle y ON y.id=p.cycle_id"
                . " WHERE y.rotated=1"
                . " AND d.id=s.decklist_id)";
        
        $this->entityManager->getConnection()->executeQuery($sql);
        
        $rotations = $this->entityManager->getRepository('AppBundle:Rotation')->findBy([], ["dateStart" => "DESC"]);
        $decklists = $this->entityManager->getRepository('AppBundle:Decklist')->findBy([]);
        
        foreach ($rotations as $rotation) {
            $output->writeln("checking " . $rotation->getName());
            
            foreach ($decklists as $decklist) {
                $confirm = $this->rotationService->isRotationCompatible($decklist, $rotation);
                
                $oldId = null;
                if ($decklist->getRotation()) {
                    $oldId = $decklist->getRotation()->getId();
                }
                
                if ($confirm && $oldId !== $rotation->getId()) {
                    $output->writeln("  updating decklist " . $decklist->getId());
                    $decklist->setRotation($rotation);
                }
            }
        }
        $this->entityManager->flush();

        $output->writeln("<info>Done</info>");
    }
}

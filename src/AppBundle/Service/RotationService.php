<?php

namespace AppBundle\Service;

use AppBundle\Entity\Cycle;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Rotation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of RotationService
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RotationService
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * [returns the first entry of the descending sorted rotations]
     * @return Rotation current Rotation
     */
    public function findCurrentRotation()
    {
        $rotations = $this->entityManager->getRepository(Rotation::class)->findBy([], ['dateStart' => 'DESC']);

        //there should always be a rotation available
        if(count($rotations) <= 0){
            throw new \Exception("No current rotation found", 1);
        }

        return $rotations[0];
    }

    public function findCompatibleRotation(Decklist $decklist)
    {
        $rotations = $this->entityManager->getRepository(Rotation::class)->findBy([], ['dateStart' => 'DESC']);

        foreach ($rotations as $rotation) {
            if ($this->isRotationCompatible($decklist, $rotation)) {
                return $rotation;
            }
        }

        return null;
    }

    /**
     * @param Decklist $decklist
     * @param Rotation $rotation
     * @return bool
     */
    public function isRotationCompatible(Decklist $decklist, Rotation $rotation)
    {
        $cycles = [];
        foreach ($decklist->getSlots() as $slot) {
            $cycles[$slot->getCard()->getPack()->getCycle()->getCode()] = 1;
        }

        return count(array_diff(array_keys($cycles), array_map(function (Cycle $cycle) {
            return $cycle->getCode();
        }, $rotation->getCycles()->toArray()))) === 0;
    }
}

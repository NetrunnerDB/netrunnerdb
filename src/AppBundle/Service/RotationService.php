<?php

namespace AppBundle\Service;

use AppBundle\Entity\Decklist;
use AppBundle\Entity\Rotation;
use Doctrine\ORM\EntityManager;

/**
 * Description of RotationService
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RotationService
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct (EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findCompatibleRotation (Decklist $decklist)
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
    public function isRotationCompatible (Decklist $decklist, Rotation $rotation)
    {
        $cycles = [];
        foreach ($decklist->getSlots() as $slot) {
            $cycles[$slot->getCard()->getPack()->getCycle()->getCode()] = 1;
        }

        return count(array_diff(array_keys($cycles), array_map(function ($cycle) {
                return $cycle->getCode();
            }, $rotation->getCycles()->toArray()))) === 0;
    }
}
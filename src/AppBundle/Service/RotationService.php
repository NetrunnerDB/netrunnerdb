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
     * Returns the first entry of the descending sorted rotations
     * @return Rotation current Rotation
     */
    public function findCurrentRotation()
    {
        $rotation = $this->entityManager->getRepository(Rotation::class)->findOneBy([], ['dateStart' => 'DESC']);

        // There should always be a rotation available.
        if (!$rotation) {
            throw new \Exception("No current rotation found", 1);
        }

        return $rotation;
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

    /**
     * @param string $code
     * @return Rotation rotation with the matching code or null if not found.
     */
    public function findRotationByCode(string $code)
    {
        return $this->entityManager->getRepository(Rotation::class)->findOneBy(['code' => $code]);
    }

}

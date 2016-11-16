<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Moderation;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

/**
 * Description of ModerationHelper
 *
 * @author cedric
 */
class ModerationHelper
{
    /** @var EntityManager */
    private $em;
    
    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getEntityManager();
    }
    
    public function getLabel($moderationStatus)
    {
        static $labels = [
            'Published',
            'Restored',
            'Trashed',
            'Deleted'
        ];
        
        return $labels[$moderationStatus];
    }
    
    public function changeStatus($decklist, $status, $user)
    {
        $previousStatus = $decklist->getModerationStatus();

        $decklist->setModerationStatus($status);
        
        $moderation = new Moderation();
        $moderation->setStatusBefore($previousStatus);
        $moderation->setStatusAfter($status);
        $moderation->setModerator($user);
        $moderation->setDecklist($decklist);
        $moderation->getDateCreation(new DateTime);
        $this->em->persist($moderation);
    }
}

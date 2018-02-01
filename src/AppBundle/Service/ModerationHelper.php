<?php

namespace AppBundle\Service;

use AppBundle\Entity\Decklist;
use AppBundle\Entity\Moderation;
use AppBundle\Entity\User;
use DateTime;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;

/**
 * Description of ModerationHelper
 *
 * @author cedric
 */
class ModerationHelper
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    
    /** @var Swift_Mailer $mailer */
    private $mailer;
    
    /** @var Twig_Environment $twig */
    private $twig;
    
    /** @var RouterInterface $router */
    private $router;
    
    /** @var LoggerInterface $logger */
    private $logger;
    
    public function __construct(EntityManagerInterface $entityManager, Swift_Mailer $mailer, Twig_Environment $twig, RouterInterface $router, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->router = $router;
        $this->logger = $logger;
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
    
    public function changeStatus(User $user, Decklist $decklist, $status, $modflag_id = null)
    {
        $previousStatus = $decklist->getModerationStatus();

        if (isset($modflag_id)) {
            $modflag = $this->entityManager->getRepository('AppBundle:Modflag')->find($modflag_id);
            if (!$modflag) {
                throw new \RuntimeException("Unknown modflag_id");
            }
            $decklist->setModflag($modflag);
        } else {
            if ($status != Decklist::MODERATION_PUBLISHED && $status != Decklist::MODERATION_RESTORED) {
                throw new \RuntimeException("modflag_id required");
            }
        }
        
        $decklist->setModerationStatus($status);
        $this->sendEmail($decklist);
        
        $moderation = new Moderation();
        $moderation->setStatusBefore($previousStatus);
        $moderation->setStatusAfter($status);
        $moderation->setModerator($user);
        $moderation->setDecklist($decklist);
        $moderation->setDateCreation(new DateTime);
        $this->entityManager->persist($moderation);
    }
    
    public function sendEmail(Decklist $decklist)
    {
        $status = $decklist->getModerationStatus();
        
        if ($status === Decklist::MODERATION_RESTORED) {
            return;
        }

        $name = "/Emails/decklist-moderation-$status.html.twig";

        $body = $this->twig->render(
            $name,
            [
                'username' => $decklist->getUser()->getUsername(),
                'decklist_name' => $decklist->getName(),
                'url' => $this->router->generate('decklist_detail', array('decklist_id' => $decklist->getId(), 'decklist_name' => $decklist->getPrettyname()), UrlGeneratorInterface::ABSOLUTE_URL),
                'reason' => $decklist->getModflag() ? $decklist->getModflag()->getReason() : "Unknown"
            ]
        );
        $this->logger->debug($body);
        $message = Swift_Message::newInstance()
                ->setSubject("Your decklist on NetrunnerDB")
                ->setFrom("moderation@netrunnerdb.com", "NetrunnerDB Moderation Team")
                ->setTo($decklist->getUser()->getEmail())
                ->setBody($body, 'text/html')
                ;
        
        $this->mailer->send($message);
    }
}

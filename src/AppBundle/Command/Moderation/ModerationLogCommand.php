<?php

namespace AppBundle\Command\Moderation;

use AppBundle\Entity\Moderation;
use AppBundle\Service\ModerationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ModerationLogCommand
 *
 * @author cedric
 */
class ModerationLogCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var ModerationHelper $moderationHelper */
    private $moderationHelper;

    public function __construct(EntityManagerInterface $entityManager, ModerationHelper $moderationHelper)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->moderationHelper = $moderationHelper;
    }

    protected function configure()
    {
        $this
            ->setName('app:moderation:log')
            ->setDescription('View the moderation log')
            ->addArgument('limit', InputArgument::OPTIONAL, "Number of lines to display", 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getArgument('limit');

        $moderationList = $this->entityManager->getRepository('AppBundle:Moderation')->findBy([], ['dateCreation' => 'DESC'], $limit);

        $table = new Table($output);
        $table->setHeaders(['Date','Mod','Before','After','Id','Deck']);
        /** @var Moderation $moderation */
        foreach ($moderationList as $moderation) {
            $table->addRow([
                $moderation->getDateCreation()->format('Y-m-d'),
                $moderation->getModerator()->getUsername(),
                $this->moderationHelper->getLabel($moderation->getStatusBefore()),
                $this->moderationHelper->getLabel($moderation->getStatusAfter()),
                $moderation->getDecklist()->getId(),
                $moderation->getDecklist()->getName()
            ]);
        }

        $table->render();
    }
}

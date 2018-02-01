<?php

namespace AppBundle\Command\Moderation;

use AppBundle\Entity\Moderation;
use AppBundle\Service\ModerationHelper;
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
    protected function configure()
    {
        $this
            ->setName('nrdb:moderation:log')
            ->setDescription('View the moderation log')
            ->addArgument('limit', InputArgument::OPTIONAL, "Number of lines to display", 10)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $helper = $this->getContainer()->get(ModerationHelper::class);
        
        $limit = $input->getArgument('limit');
                
        $moderationList = $em->getRepository('AppBundle:Moderation')->findBy([], ['dateCreation' => 'DESC'], $limit);
        
        $table = new Table($output);
        $table->setHeaders(['Date','Mod','Before','After','Id','Deck']);
        /* @var $moderation Moderation */
        foreach ($moderationList as $moderation) {
            $table->addRow([
                $moderation->getDateCreation()->format('Y-m-d'),
                $moderation->getModerator()->getUsername(),
                $helper->getLabel($moderation->getStatusBefore()),
                $helper->getLabel($moderation->getStatusAfter()),
                $moderation->getDecklist()->getId(),
                $moderation->getDecklist()->getName()
            ]);
        }
        
        $table->render();
    }
}

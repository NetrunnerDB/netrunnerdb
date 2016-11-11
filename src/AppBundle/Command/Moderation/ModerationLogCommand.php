<?php

namespace AppBundle\Command\Moderation;

use AppBundle\Entity\Moderation;
use AppBundle\Helper\ModerationHelper;
use Doctrine\ORM\EntityManager;
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
    /** @var EntityManager */
    private $em;
    /** @var ModerationHelper */
    private $helper;
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
        $this->input = $input;
        $this->output = $output;
     
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        $this->helper = $this->getContainer()->get('moderation_helper');
        
        $limit = $input->getArgument('limit');
                
        $moderationList = $this->em->getRepository('AppBundle:Moderation')->findBy([], ['dateCreation' => 'DESC'], $limit);
        
        $table = new Table($output);
        $table->setHeaders(['Date','Mod','Before','After','Deck']);
        /* @var $moderation Moderation */
        foreach($moderationList as $moderation) {
            $table->addRow([
                $moderation->getDateCreation()->format('Y-m-d'),
                $moderation->getModerator()->getUsername(),
                $this->helper->getLabel($moderation->getStatusBefore()),
                $this->helper->getLabel($moderation->getStatusAfter()),
                $moderation->getDecklist()->getName()
            ]);
        }
        
        $table->render();
    }
}

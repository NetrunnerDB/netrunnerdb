<?php

namespace AppBundle\Command\Moderation;

use AppBundle\Entity\Decklist;
use AppBundle\Entity\Moderation;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ModerationActionCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this
            ->setName('nrdb:moderation:action')
            ->setDescription('Changes the moderation status of a decklist')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        
        $this->helper = $this->getHelper('question');

        /* @var $em EntityManager */
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        $user = $this->em->getRepository('AppBundle:User')->find(1);
        
        /* @var $moderationHelper Moderation */
        $this->moderationHelper = $this->getContainer()->get('moderation_helper');
        
        $decklistId = $this->helper->ask($input, $output, new Question('Please enter the id of the decklist: '));
        $decklist = $this->getDecklist($decklistId);
        
        $this->showStatus($decklist);

        $newStatus = $this->getNewStatus();
        $this->moderationHelper->changeStatus($decklist, $newStatus, $user);

        $this->em->flush();
        $this->em->refresh($decklist);

        $this->showStatus($decklist);
    }
    
    protected function showStatus($decklist)
    {
        $this->output->writeln('<info>Decklist: '.$decklist->getName().'. Current moderation status: '.$this->moderationHelper->getLabel($decklist->getModerationStatus()).'</info>');
    }
            
    
    /**
     * 
     * @return integer
     */
    protected function getNewStatus()
    {
        $choices = ['Published', 'Restored', 'Trashed'];
        $question = new ChoiceQuestion(
            'Which status do you want to set on the decklist? ',
            $choices
        );
        $question->setErrorMessage('Status %s is invalid.');

        $answer = $this->helper->ask($this->input, $this->output, $question);
        
        return array_search($answer, $choices);
    }
    
    /**
     * 
     * @param integer $decklistId
     * @return Decklist
     */
    protected function getDecklist($decklistId)
    {
        $decklist = $this->em->getRepository('AppBundle:Decklist')->find($decklistId);
        if(!$decklist) {
            $this->output->writeln('<error>Not Found</error>');
            die;
        }
        return $decklist;
    }
           
            
}
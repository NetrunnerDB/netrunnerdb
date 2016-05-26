<?php

namespace Netrunnerdb\BuilderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoveDecklistCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('nrdb:delete-decklist')
            ->setDescription('Remove one decklist')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Id of the decklist'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$decklist_id = $input->getArgument('id');

        /* @var $em \Doctrine\ORM\EntityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        /* @var $decklistManager \Netrunnerdb\BuilderBundle\Services\Decklists */
        $decklistManager = $this->getContainer()->get('decklists');

		$decklist = $entityManager->getRepository('NetrunnerdbBuilderBundle:Decklist')->find($decklist_id);
		
		$decklistManager->removeConstraints($decklist);
        $entityManager->remove($decklist);
		
		$entityManager->flush();
        
        $output->writeln("Done.");
    }
}
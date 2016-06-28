<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Entity\Legality;

class LegalityCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('nrdb:legality')
            ->setDescription('Compute decklist legality for a MWL')
            ->addArgument(
                'mwl_id',
                InputArgument::REQUIRED,
                'Id of the MWL'
            )
            ->addOption(
            		'decklist',
            		'd',
            		InputOption::VALUE_OPTIONAL,
            		'Id of the decklist'
            )
            
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	/* @var $entityManager \Doctrine\ORM\EntityManager */
    	$entityManager = $this->getContainer()->get('doctrine')->getManager();
    	
    	/* @var $judge \AppBundle\Service\Judge */
    	$judge = $this->getContainer()->get('judge');
    	
    	$mwl_id = $input->getArgument('mwl_id');
    	$mwl = $entityManager->getRepository('AppBundle:Mwl')->find($mwl_id);
    	
    	if(!$mwl) 
    	{
    		$output->writeln("<error>MWL not found</error>");
    		return;
    	}
    	
    	$decklist_id = $input->getOption('decklist');
    	if($decklist_id) 
    	{
    		$decklist = $entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
    		if(!$decklist)
    		{
    			$output->writeln("<error>Decklist not found</error>");
    			return;
    		}
    		$legality = $entityManager->getRepository('AppBundle:Legality')->findOneBy(['mwl' => $mwl, 'decklist' => $decklist]);
    		if(!$legality)
    		{
    			$legality = new Legality();
    			$legality->setDecklist($decklist);
    			$legality->setMwl($mwl);
    			$entityManager->persist($legality);
    		}
    		$judge->computeLegality($legality);
    		$entityManager->flush();
    		if($legality->getIsLegal())
    		{
    			$output->writeln("<info>Done. Decklist is legal.</info>");
    		}
    		else
    		{
    			$output->writeln("<info>Done. Decklist is NOT legal.</info>");
    		}
    		return;
    	}
    	
    	$countDql = "SELECT COUNT(d) 
					FROM AppBundle:Decklist d 
					WHERE NOT EXISTS (
						SELECT l 
						FROM AppBundle:Legality l
						WHERE l.decklist=d AND l.mwl=?1
    				)";
    	$countQuery = $entityManager->createQuery($countDql)->setParameter(1, $mwl);
    	$count = $countQuery->getSingleResult()[1];
    	$output->writeln("<comment>Found $count decklists to analyze</comment>");
    	
    	$progress = new ProgressBar($output, $count);
    	$progress->setRedrawFrequency(10);
    	$progress->start();
    	
    	$fetchDql = str_replace('COUNT(d)', 'd', $countDql);
    	$fetchQuery = $entityManager->createQuery($fetchDql)->setParameter(1, $mwl)->setMaxResults(1);
    	while($decklist = $fetchQuery->getSingleResult()) 
    	{
   			$legality = new Legality();
   			$legality->setDecklist($decklist);
   			$legality->setMwl($mwl);
  			$judge->computeLegality($legality);
  			$entityManager->persist($legality);
   			$entityManager->flush();
   			$entityManager->detach($legality);
    		$entityManager->detach($decklist);
    		
    		$progress->advance();
    	}
    	
    	$progress->finish();
    	$output->writeln("<info>Done</info>");
    	 
    }
}
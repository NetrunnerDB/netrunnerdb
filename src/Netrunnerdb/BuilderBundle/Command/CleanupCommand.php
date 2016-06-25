<?php

namespace Netrunnerdb\BuilderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CleanupCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('nrdb:cleanup')
            ->setDescription('Remove old, unused decklists')
            ->addOption(
                'months',
                'm',
                InputOption::VALUE_REQUIRED,
                'Min number of months old'
            )
            ->addOption(
                'votes',
                'l',
                InputOption::VALUE_REQUIRED,
                'Max number of votes'
            )
            ->addOption(
                'favorites',
                'f',
                InputOption::VALUE_REQUIRED,
                'Max number of favorites'
            )
            ->addOption(
                'comments',
                'c',
                InputOption::VALUE_REQUIRED,
                'Max number of comments'
            )
            ->addOption(
                'desclength',
                'd',
                InputOption::VALUE_REQUIRED,
                'Max size of description'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $options['dotw'] = 0;

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /* @var $decklistManager \Netrunnerdb\BuilderBundle\Services\Decklists */
        $decklistManager = $this->getContainer()->get('decklists');

        $qb = $em->createQueryBuilder();
        $qb->select('count(d)')
            ->from('NetrunnerdbBuilderBundle:Decklist', 'd')
            ->where('d.dotw=:dotw');

        $totalCount = $qb->getQuery()->setParameters(array_intersect_key($options, array_flip(['dotw'])))->getSingleScalarResult();
        $qb->andWhere('d.dateCreation < DATE_SUB(CURRENT_DATE(), :months, \'month\')');
        $periodCount = $qb->getQuery()->setParameters(array_intersect_key($options, array_flip(['dotw', 'months'])))->getSingleScalarResult();


        $qb->andWhere('d.nbvotes <= :votes')
            ->andWhere('d.nbfavorites <= :favorites')
            ->andWhere('d.nbcomments <= :comments')
            ->andWhere('LENGTH(d.rawdescription) <= :desclength');

        $queryCount = $qb->getQuery()->setParameters(array_intersect_key($options, array_flip(['dotw', 'months', 'votes', 'favorites', 'comments', 'desclength'])))->getSingleScalarResult();

        $periodPct = intval(100 * $queryCount / $periodCount);
        $totalPct = intval(100 * $queryCount / $totalCount);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("You selected $queryCount decklists ($periodPct% of period, $totalPct% of total). Do you really want to remove them? (y/N) ", false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        
        $qb->select('d');
        $result = $qb->getQuery()->setParameters(array_intersect_key($options, array_flip(['dotw', 'months', 'votes', 'favorites', 'comments', 'desclength'])))->getResult();

		$progress = new ProgressBar($output, $queryCount);
		
        foreach($result as $decklist)
        {
            $decklistManager->removeConstraints($decklist);
            $progress->advance();
        }

        $progress->finish();
        $em->flush();
        
        $output->writeln("\nRemoved $queryCount constraints.");
        
        $progress = new ProgressBar($output, $queryCount);
        
        foreach($result as $decklist)
        {
        	$em->remove($decklist);
        	$progress->advance();
        }
        
        $progress->finish();
        $em->flush();
        
        $output->writeln("\nRemoved $queryCount decklists ($periodPct% of period, $totalPct% of total).");
    }
}
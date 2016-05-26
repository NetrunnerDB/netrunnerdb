<?php

namespace Netrunnerdb\BuilderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Netrunnerdb\BuilderBundle\Entity\Review;
use Netrunnerdb\BuilderBundle\Entity\Reviewcomment;

class MwlApplicationCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this
        ->setName('nrdb:mwl')
        ->setDescription('Checks to see if a new MWL becomes active')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();


        $now = new \DateTime();

        $qb = $em->createQueryBuilder();
        $qb->select('m')
            ->from('NetrunnerdbBuilderBundle:Mwl', 'm')
            ->orderBy('m.dateStart', 'DESC');
        $query = $qb->getQuery();

        /* @var $mwl \Netrunnerdb\BuilderBundle\Entity\Mwl */
        $list = $query->getResult();

        if(!count($list))
        {
            $output->writeln("<error>No MWL in database</error>");
            return;
        }

        $mwl = array_shift($list);
        if($mwl->getActive() || $mwl->getDateStart() > $now)
        {
            $output->writeln("Nothing to do");
            return;
        }

        $mwl->setActive(TRUE);
        $output->writeln($mwl->getName() . " set as ACTIVE");

        while($mwl = array_shift($list))
        {
            if($mwl->getActive())
            {
                $mwl->setActive(FALSE);
                $output->writeln($mwl->getName() . " set as INACTIVE");
            }
        }

        $em->flush();
    }
}
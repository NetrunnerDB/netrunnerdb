<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
            ->from('AppBundle:Mwl', 'm')
            ->orderBy('m.dateStart', 'DESC');
        $query = $qb->getQuery();

        /* @var $mwl \AppBundle\Entity\Mwl */
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
<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LegalityActiveMwlCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
                ->setName('nrdb:legality:active-mwl')
                ->setDescription('Checks to see if a new MWL becomes active')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('m')
                ->from('AppBundle:Mwl', 'm')
                ->orderBy('m.dateStart', 'DESC');
        $query = $qb->getQuery();

        /* @var $mwl \AppBundle\Entity\Mwl */
        $list = $query->getResult();

        if (!count($list)) {
            $output->writeln("<error>No MWL in database</error>");
            return;
        }

        $mwl = array_shift($list);
        if ($mwl->getActive() || $mwl->getDateStart() > $now) {
            $output->writeln("Nothing to do");
            return;
        }

        $mwl->setActive(true);
        $output->writeln($mwl->getName() . " set as ACTIVE");

        while ($mwl = array_shift($list)) {
            if ($mwl->getActive()) {
                $mwl->setActive(false);
                $output->writeln($mwl->getName() . " set as INACTIVE");
            }
        }

        $this->entityManager->flush();
    }
}

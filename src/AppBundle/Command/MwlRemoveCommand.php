<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Description of RemoveMwlCommand
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class MwlRemoveCommand extends ContainerAwareCommand
{

    protected function configure ()
    {
        $this
                ->setName('nrdb:mwl:remove')
                ->setDescription('Remove a MWL')
                ->addArgument(
                        'id', InputArgument::REQUIRED, 'Id of the mwl'
                )
        ;
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $mwl = $entityManager->getRepository('AppBundle:Mwl')->find($id);
        if(!$mwl) {
            throw new Exception("Cannot find MWL");
        }
        
        /* @var $list_deck \AppBundle\Entity\Deck[] */
        $list_deck = $entityManager->getRepository('AppBundle:Deck')->findBy(array(
            'mwl' => $mwl
        ));
        
        foreach($list_deck as $deck) {
            $deck->setMwl(null);
        }
        
        $entityManager->flush();
        
        $entityManager->remove($mwl);
        
        $entityManager->flush();
        
        $output->writeln("Done.");
    }

}

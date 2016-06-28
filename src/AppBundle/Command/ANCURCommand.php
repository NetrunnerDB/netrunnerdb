<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ANCURCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
        ->setName('nrdb:ancur')
        ->setDescription('Verify links to ANCUR')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->verify($output);
        
        $output->writeln('done');
    }

    private function verify(OutputInterface $output)
    {

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $cards = $em->getRepository('AppBundle:Card')->findAll();
        /* @var $card \AppBundle\Entity\Card */
        foreach($cards as $card) {
            $url = $card->getAncurLink();
            $output->writeln("$url");
            continue;
            $headers = get_headers($url);
            if($headers) {
                $http = array_shift($headers);
                $output->writeln("$url $http");
            } else {
                $output->writeln("$url cannot reach");
            }
        }
        
    }
    
}
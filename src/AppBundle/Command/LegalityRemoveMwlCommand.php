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
class LegalityRemoveMwlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
                ->setName('nrdb:legality:remove-mwl')
                ->setDescription('Remove a MWL')
                ->addArgument(
                        'mwl_code',
                    InputArgument::REQUIRED,
                    'Code of the MWL'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $mwl_code = $input->getArgument('mwl_code');
        $mwl = $entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwl_code]);
        if (!$mwl) {
            throw new \Exception("MWL not found");
        }

        /* @var $list_deck \AppBundle\Entity\Deck[] */
        $list_deck = $entityManager->getRepository('AppBundle:Deck')->findBy(array(
            'mwl' => $mwl
        ));

        foreach ($list_deck as $deck) {
            $deck->setMwl(null);
        }

        $entityManager->flush();

        $entityManager->remove($mwl);

        $entityManager->flush();

        $output->writeln("Done.");
    }
}

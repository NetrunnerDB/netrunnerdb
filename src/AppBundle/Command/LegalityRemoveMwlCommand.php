<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
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
            ->setName('nrdb:legality:remove-mwl')
            ->setDescription('Remove a MWL')
            ->addArgument(
                'mwl_code',
                InputArgument::REQUIRED,
                'Code of the MWL'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mwl_code = $input->getArgument('mwl_code');
        $mwl = $this->entityManager->getRepository('AppBundle:Mwl')->findOneBy(['code' => $mwl_code]);
        if (!$mwl) {
            throw new \Exception("MWL not found");
        }

        /* @var $list_deck \AppBundle\Entity\Deck[] */
        $list_deck = $this->entityManager->getRepository('AppBundle:Deck')->findBy([
            'mwl' => $mwl,
        ]);

        foreach ($list_deck as $deck) {
            $deck->setMwl(null);
        }

        $this->entityManager->flush();

        $this->entityManager->remove($mwl);

        $this->entityManager->flush();

        $output->writeln("Done.");
    }
}

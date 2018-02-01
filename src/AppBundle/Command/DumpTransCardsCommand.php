<?php

namespace AppBundle\Command;

use AppBundle\Entity\Card;
use AppBundle\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DumpTransCardsCommand extends ContainerAwareCommand
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
        ->setName('nrdb:dump:trans:cards')
        ->setDescription('Dump Translations of Cards from a Pack for a Locale')
        ->addArgument(
                'pack_code',
                InputArgument::REQUIRED,
                "Pack Code"
        )
        ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                "Locale"
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pack_code = $input->getArgument('pack_code');
        $locale = $input->getArgument('locale');
        
        $pack = $this->entityManager->getRepository('AppBundle:Pack')->findOneBy(['code' => $pack_code]);
        
        if (!$pack) {
            throw new \Exception("Pack [$pack_code] cannot be found.");
        }
        
        $this->entityManager->clear();

        /** @var CardRepository $repository */
        $repository = $this->entityManager->getRepository('AppBundle:Card');
        
        $qb = $repository->setDefaultLocale($locale)->createQueryBuilder('c')->where('c.pack = :pack')->setParameter('pack', $pack)->orderBy('c.code');
        
        $cards = $repository->getResult($qb);
        
        $arr = [];

        /** @var Card $card */
        foreach ($cards as $card) {
            $data = [];
            $data['code'] = $card->getCode();
            if ($flavor = $card->getFlavor()) {
                $data['flavor'] = $flavor;
            }
            if ($keywords = $card->getKeywords()) {
                $data['keywords'] = $keywords;
            }
            if ($text = $card->getText()) {
                $data['text'] = $text;
            }
            $data['title'] = $card->getTitle();
            $arr[] = $data;
        }
        
        $output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $output->writeln("");
    }
}

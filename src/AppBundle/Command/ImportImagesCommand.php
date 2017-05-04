<?php

namespace AppBundle\Command;

/**
 * Description of ImportImagesCommand
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class ImportImagesCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     *
     * @var string
     */
    private $prefix;

    /**
     *
     * @var integer
     */
    private $position;

    /**
     *
     * @var string
     */
    private $imagesPath;

    protected function configure ()
    {
        $this
                ->setName('nrdb:import:images')
                ->setDescription('Import missing images from cardgamedb.com')
                ->addOption("base_uri", null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, "URI of the directory", "http://www.cardgamedb.com/forums/uploads/an/")
                ->addOption("prefix", null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, "Prefix of the images files", "med_ADN")
                ->addArgument("code", \Symfony\Component\Console\Input\InputArgument::REQUIRED, "Code of the pack to import")
                ->addArgument("position", \Symfony\Component\Console\Input\InputArgument::REQUIRED, "Ordinal number of the pack to import")
        ;
    }

    protected function execute (\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $baseUri = $input->getOption("base_uri");
        $prefix = $input->getOption("prefix");
        $packCode = $input->getArgument("code");
        $position = $input->getArgument("position");

        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
        $this->client = new \GuzzleHttp\Client(['base_uri' => $baseUri]);
        $this->prefix = $prefix;
        $this->position = $position;
        $this->imagesPath = $this->getContainer()->getParameter('images_path');

        $pack = $this->em->getRepository(\AppBundle\Entity\Pack::class)->findBy(['code' => $packCode]);
        if (!$pack) {
            throw new \Exception("Pack not found");
        }
        /* @var $cards \AppBundle\Entity\Card[] */
        $cards = $this->em->getRepository(\AppBundle\Entity\Card::class)->findBy(['pack' => $pack]);
        foreach ($cards as $card) {
            $output->writeln("Downloading " . $card->toString());
            $this->import($card);
        }
    }

    private function import (\AppBundle\Entity\Card $card)
    {
        $imagePath = $this->imagesPath . "/" . $card->getCode() . ".png";
        if (file_exists($imagePath)) {
            return;
        }
        $fh = fopen($imagePath, "w");
        if (!$fh) {
            throw new \Exception("Cannot open file $imagePath");
        }
        $imageURI = sprintf("%s%d_%d.png", $this->prefix, $this->position, $card->getPosition());
        $response = $this->client->request('GET', $imageURI);

        if ($response->getStatusCode() === 200) {
            fwrite($fh, $response->getBody()->getContents(), $response->getBody()->getSize());
        }
        fclose($fh);
    }

}

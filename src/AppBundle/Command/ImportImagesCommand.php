<?php

namespace AppBundle\Command;

use AppBundle\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ImportImagesCommand
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class ImportImagesCommand extends ContainerAwareCommand
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
            ->setName('app:import:images')
            ->setDescription('Import missing images from cardgamedb.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client([
            'http_errors' => false,
        ]);

        /** @var Card[] $cards */
        $cards = $this->entityManager->getRepository(Card::class)->findBy(['imageUrl' => null]);

        foreach ($cards as $card) {
            $ffgId = $card->getPack()->getFfgId();
            if ($ffgId === null) {
                continue;
            }

            $position = $card->getPosition();
            if ($card->getCode() === '09001') {
                $position = '1a';
            }

            $url = sprintf(
                'http://www.cardgamedb.com/forums/uploads/an/med_ADN%d_%s.png',
                $ffgId,
                $position
            );
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() === 200) {
                $card->setImageUrl($url);
                $output->writeln(sprintf('Found image for %s at url %s', $card, $url));
            } else {
                $output->writeln(sprintf('<error>Image missing for %s at url %s</error>', $card, $url));
            }
        }

        $this->entityManager->flush();
    }
}

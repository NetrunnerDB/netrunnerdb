<?php

namespace AppBundle\Command;

use AppBundle\Entity\Card;
use GuzzleHttp\Client;

/**
 * Description of ImportImagesCommand
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class ImportImagesCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    protected function configure ()
    {
        $this
            ->setName('nrdb:import:images')
            ->setDescription('Import missing images from cardgamedb.com');
    }

    protected function execute (\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $client = new Client([
            'http_errors' => false,
        ]);

        /** @var Card[] $cards */
        $cards = $em->getRepository(Card::class)->findBy(['imageUrl' => null]);

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
                $output->writeln(sprintf('Found image for %s at url %s', $card->toString(), $url));
            } else {
                $output->writeln(sprintf('<error>Image missing for %s at url %s</error>', $card->toString(), $url));
            }
        }

        $em->flush();
    }
}

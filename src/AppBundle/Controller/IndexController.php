<?php

namespace AppBundle\Controller;

use AppBundle\Service\DecklistManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param DecklistManager        $decklistManager
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Request $request, EntityManagerInterface $entityManager, DecklistManager $decklistManager)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        // decklist of the week
        $dbh = $entityManager->getConnection();
        $rows = $dbh->executeQuery("SELECT decklist FROM highlight WHERE id=?", [1])->fetchAll();
        $decklist = count($rows) ? json_decode($rows[0]['decklist']) : null;

        // TODO(plural): Remove this lookup once the DOTW contains UUID by default.
        if ($decklist != null) {
            $decklist_object = $entityManager->getRepository('AppBundle:Decklist')->find($decklist->{'id'});
            $decklist->{'uuid'} = $decklist_object->getUuid();
        }
        // recent decklists
        $decklists_recent = $decklistManager->recent(0, 10, false)['decklists'];

        // load site updates
        $updates = [];
        if (file_exists('update_log.txt')) {
            $file = fopen('update_log.txt', 'r');
            $update = null;
            if ($file) {
                while (($line = fgets($file)) !== false) {
                    $line = trim($line);
                    if (empty($line))
                        continue;
                    if ($line[0] !== '-') {
                        if ($update != null) {
                            $updates[] = $update;
                        }
                        $update = array('date' => $line, 'entries' => []);
                    } else if ($update !== null) {
                        $update['entries'][] = trim(substr($line, 1));
                    }
                }
                $updates[] = $update;
            }
            fclose($file);
        }

        return $this->render(

            'Default/index.html.twig',
            [
                'pagetitle'       => "Android: Netrunner Cards and Deckbuilder",
                'pagedescription' => "Build your deck for Android: Netrunner, the LCG by Fantasy Flight Games. Browse the cards and the thousand of decklists submitted by the community. Publish your own decks and get feedback.",
                'decklists'       => $decklists_recent,
                'decklist'        => $decklist,
                'url'             => $request->getRequestUri(),
                'updates'         => $updates,
            ],

            $response

        );
    }
}

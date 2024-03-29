<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class HighlightCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function saveHighlight(string $decklist_uuid = null)
    {
        $dbh = $this->entityManager->getConnection();

        if (null !== $decklist_uuid) {
            $rows = $dbh
                ->executeQuery(
                    "SELECT
                       d.id,
                       d.uuid,
                       d.date_update,
                       d.name,
                       d.prettyname,
                       d.date_creation,
                       d.rawdescription,
                       d.description,
                       d.precedent_decklist_id precedent,
                       u.id user_id,
                       u.username,
                       u.faction usercolor,
                       u.reputation,
                       u.donation,
                       c.code identity_code,
                       f.code faction_code,
                       d.nbvotes,
                       d.nbfavorites,
                       d.nbcomments,
                       m.code as mwl_code
                     FROM decklist d
                       JOIN user u ON d.user_id=u.id
                       JOIN card c ON d.identity_id=c.id
                       JOIN faction f ON d.faction_id=f.id
                       JOIN mwl m ON d.mwl_id = m.id
                     WHERE d.uuid=?
                       ",
                    [$decklist_uuid]
                )->fetchAll();
        } else {
            $rows = $dbh
                ->executeQuery(
                    "SELECT
                       d.id,
                       d.uuid,
                       d.date_update,
                       d.name,
                       d.prettyname,
                       d.date_creation,
                       d.rawdescription,
                       d.description,
                       d.precedent_decklist_id precedent,
                       u.id user_id,
                       u.username,
                       u.faction usercolor,
                       u.reputation,
                       u.donation,
                       c.code identity_code,
                       f.code faction_code,
                       d.nbvotes,
                       d.nbfavorites,
                       d.nbcomments,
                       m.code as mwl_code
                     FROM decklist d
                       JOIN user u ON d.user_id=u.id
                       JOIN card c ON d.identity_id=c.id
                       JOIN faction f ON d.faction_id=f.id
                       JOIN mwl m ON d.mwl_id = m.id
                     WHERE d.date_creation > date_sub( current_date, INTERVAL 7 DAY )
                       AND u.enabled=1
                       AND d.moderation_status=0
                     ORDER BY nbvotes DESC , nbcomments DESC
                     LIMIT 0,1
                           ",
                    []
                )->fetchAll();
        }

        if (empty($rows)) {
            return false;
        }

        $decklist = $rows[0];

        $cards = $dbh
            ->executeQuery(
                "SELECT
                   c.code card_code,
                   s.quantity qty
                 FROM decklistslot s
                   JOIN card c ON s.card_id=c.id
                 WHERE s.decklist_id=?
                 ORDER BY c.code ASC",
                [$decklist['id']]
            )->fetchAll();

        $decklist['cards'] = $cards;

        $json = json_encode($decklist);
        $dbh->executeQuery("INSERT INTO highlight (id, decklist) VALUES (?,?) ON DUPLICATE KEY UPDATE decklist=values(decklist)", [1, $json]);

        $dotw = $dbh->executeQuery("SELECT max(dotw) FROM decklist")->fetchColumn(0);
        $next_dotw = intval($dotw) + 1;
        $dbh->executeQuery("UPDATE decklist SET dotw=? WHERE ID=?", [$next_dotw, $decklist['id']]);

        return true;
    }

    protected function configure()
    {
        $this
            ->setName('app:highlight')
            ->setDescription('Save decklist of the week')
            ->addArgument(
                'decklist_uuid',
                InputArgument::OPTIONAL,
                'UUID for Decklist'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $decklist_uuid = $input->getArgument('decklist_uuid');
        $result = $this->saveHighlight($decklist_uuid);
        $output->writeln(date('c') . " " . ($result ? "Success" : "Failure"));
    }
}

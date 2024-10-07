<?php

namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Decklist;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class DecklistManager
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function getLimitedQueryRowsWithCounts(EntityManagerInterface $entityManager, string $baseQuery, int $start, int $limit, array $params) {

        $dbh = $entityManager->getConnection();

        $rows = $dbh->executeQuery("$baseQuery LIMIT $start, $limit", $params)->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT COUNT(*) FROM ($baseQuery) AS t", $params)->fetch(\PDO::FETCH_NUM)[0];

        return [
            "rows" => $rows,
            "count" => $count
        ];
    }

    /**
     * returns the list of decklist favorited by user
     *
     * @param int $user_id
     * @param int $start
     * @param int $limit
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function favorites(int $user_id, int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts(
            $this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                join favorite f on f.decklist_id=d.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where f.user_id=?
                and d.moderation_status in (0,1)
                order by date_creation desc",
            $start, $limit, [ $user_id ]);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of decklists published by user
     *
     * @param int $user_id
     * @param int $start
     * @param int $limit
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function by_author(int $user_id, int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.user_id=?
                and d.moderation_status in (0,1,2)
                order by date_creation desc",
            $start, $limit, [ $user_id ]);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of recent decklists with large number of votes
     * @param integer $limit
     * @return array
     */
    public function popular(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments,
                DATEDIFF(CURRENT_DATE, d.date_creation) nbjours
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.date_creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                and d.moderation_status in (0,1)
                order by 2*nbvotes/(1+nbjours*nbjours) DESC, nbvotes desc, nbcomments desc",
            $start, $limit, []);
        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of decklists tagged with dotw>0
     * @param integer $limit
     * @return array
     */
    public function dotw(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
              d.id,
              d.uuid,
              d.name,
              d.prettyname,
              d.date_creation,
              d.user_id,
              d.tournament_id,
              t.description tournament,
              r.name rotation,
              u.username,
              u.faction usercolor,
              u.reputation,
              u.donation,
              c.code,
              c.title identity,
              c.image_url identity_url,
              p.name lastpack,
              d.nbvotes,
              d.nbfavorites,
              d.nbcomments
            from decklist d
              join user u on d.user_id=u.id
              join card c on d.identity_id=c.id
              join pack p on d.last_pack_id=p.id
              left join tournament t on d.tournament_id=t.id
              left join rotation r on d.rotation_id=r.id
            where dotw > 0
            order by dotw desc",
        $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of decklists with most number of votes
     * @param integer $limit
     * @return array
     */
    public function halloffame(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where nbvotes > 10
                and d.moderation_status in (0,1)
                order by nbvotes desc, date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of decklists with large number of recent comments
     * @param integer $limit
     * @return array
     */
    public function hottopics(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments,
                (select count(*) from comment where comment.decklist_id=d.id and DATEDIFF(CURRENT_DATE, comment.date_creation)<1) nbrecentcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.nbcomments > 1
                and d.moderation_status in (0,1)
                order by nbrecentcomments desc, date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of decklists with a non-null tournament
     * @param integer $limit
     * @return array
     */
    public function tournaments(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.tournament_id is not null
                and d.moderation_status in (0,1)
                order by date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    // TODO(plural): Remove this function if truly unused.
    /**
     * returns the list of decklists of chosen faction
     * @param integer $limit
     * @return array
     */
    public function faction(string $faction_code, int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                join faction f on d.faction_id=f.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where f.code=?
                and d.moderation_status in (0,1)
                order by date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    // TODO(plural): Remove this function if truly unused.
    /**
     * returns the list of decklists of chosen datapack
     * @param integer $limit
     * @return array
     */
    public function lastpack(string $pack_code, int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where p.code=?
                and d.moderation_status in (0,1)
                order by date_creation desc",
            $start, $limit, [ $pack_code ]);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of recent decklists
     * @param integer $limit
     * @return array
     */
    public function recent(int $start = 0, int $limit = 30, bool $includeEmptyDesc = true)
    {
        $additional_clause = $includeEmptyDesc ? "" : "and d.rawdescription!=''";

        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.date_creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                and d.moderation_status in (0,1)
                $additional_clause
                order by date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of trashed decklists
     * @param integer $limit
     * @return array
     */
    public function trashed(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.moderation_status=2
                order by date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns the list of restored decklists
     * @param integer $limit
     * @return array
     */
    public function restored(int $start = 0, int $limit = 30)
    {
        $results = $this->getLimitedQueryRowsWithCounts($this->entityManager,
            "SELECT
                d.id,
                d.uuid,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                r.name rotation,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                c.image_url identity_url,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                left join rotation r on d.rotation_id=r.id
                where d.moderation_status=1
                order by date_creation desc",
            $start, $limit, []);

        return [
            "count"     => $results['count'],
            "decklists" => $results['rows'],
        ];
    }

    /**
     * returns a list of decklists according to search criteria
     * @param integer $limit
     * @return array
     */
    public function find(int $start = 0, int $limit = 30, Request $request, $cardsData)
    {
        $dbh = $this->entityManager->getConnection();

        $cardRepository = $this->entityManager->getRepository('AppBundle:Card');

        $cards_code = $request->query->get('cards');
        if (!is_array($cards_code)) {
            $cards_code = [];
        }
        $faction_code = filter_var($request->query->get('faction'), FILTER_SANITIZE_STRING);
        $author_name = filter_var($request->query->get('author'), FILTER_SANITIZE_STRING);
        $decklist_title = filter_var($request->query->get('title'), FILTER_SANITIZE_STRING);
        $sort = $request->query->get('sort');
        $packs = $request->query->get('packs');
        $mwl_code = $request->query->get('mwl_code');
        $rotation_id = $request->query->get('rotation_id');
        $is_legal = $request->query->get('is_legal');
        if ($is_legal === null || $is_legal === '') {
            $is_legal = null;
        } else {
            $is_legal = boolval($is_legal);
        }

        if (!is_array($packs)) {
            $packs = $dbh->executeQuery("SELECT code FROM pack")->fetchAll(\PDO::FETCH_COLUMN);
        }

        if ($faction_code === "corp" || $faction_code === "runner") {
            $side_code = $faction_code;
            unset($faction_code);
        }

        // $ctes will hold the individual Common Table Expressions that make up the full decklist search query.
        // While there will always be at least 1 CTE, there may be up to 4 depending on if packs and individual cards are specified.
        $ctes = [];
        $joins = [];
        $wheres = [];
        $params = [];
        $types = [];

        $join = '';
        $group_by = '';
        $group_by_count = 0;
        $ors = [];

        // If any cards are specified, they will be the latest printing IDs.
        // We need to use those to get all of the card ids for every printing of those cards.
        // Then, find all the decklist ids with all of those cards.
        if (count($cards_code)) {
            $card_ids = [];
            $card_versions = $cardsData->get_versions_by_code($cards_code);
            foreach ($card_versions as $card) {
                $card_ids[] = $card->getId();
            }
            $ctes[] = "
                decklists_with_desired_cards AS (
                    SELECT
                        decklist_id
                    FROM
                        decklistslot
                    WHERE
                        card_id IN (?)
                    GROUP BY
                        decklist_id
                    HAVING
                        COUNT(*) = ?
                )
            ";
            $params[] = array_unique($card_ids);
            $types[] = Connection::PARAM_INT_ARRAY;
            // Uses $cards_code because that is the number the user specified, not the number of printings.
            $params[] = count($cards_code);
            $types[] = \PDO::PARAM_INT;
        }

        // If packs are specified, things get complicated because we will be filtering OUT decklists that contain cards NOT IN any of the selected packs.
        if (count($packs)) {
            // First get the ids for all the unwanted cards.
            $ctes[] = "
                unwanted_cards AS (
                    SELECT
                        card.id AS card_id
                    from
                        card
                        join pack on card.pack_id = pack.id
                    WHERE
                        pack.code NOT IN (?)
                )";
                // Next, get the decklist_id for every deck that contains any of those unwanted cards.
                $ctes[] = "unwanted_decklists AS (
                    SELECT DISTINCT decklist_id
                    FROM decklistslot WHERE card_id IN (SELECT card_id FROM unwanted_cards)
                )";
            $params[] = array_unique($packs);
            $types[] = Connection::PARAM_STR_ARRAY;
            $joins[] = "left join unwanted_decklists AS ud ON d.id = ud.decklist_id";
            $wheres[] = "ud.decklist_id IS NULL";
        }
        $join = implode("\n", $joins);

        if (!empty($side_code)) {
            $wheres[] = 's.code=?';
            $params[] = $side_code;
            $types[] = \PDO::PARAM_STR;
        }
        if (!empty($faction_code)) {
            $wheres[] = 'f.code=?';
            $params[] = $faction_code;
            $types[] = \PDO::PARAM_STR;
        }
        if (!empty($author_name)) {
            $wheres[] = 'u.username=?';
            $params[] = $author_name;
            $types[] = \PDO::PARAM_STR;
        }
        if (!empty($decklist_title)) {
            $wheres[] = 'd.name like ?';
            $params[] = '%' . $decklist_title . '%';
            $types[] = \PDO::PARAM_STR;
        }
        if (!empty($mwl_code)) {
            $wheres[] = 'exists(select * from legality join mwl on legality.mwl_id=mwl.id where legality.decklist_id=d.id and mwl.code=? and legality.is_legal=1)';
            $params[] = $mwl_code;
            $types[] = \PDO::PARAM_STR;
        }
        if (!empty($rotation_id)) {
            $wheres[] = 'd.rotation_id=?';
            $params[] = $rotation_id;
            $types[] = \PDO::PARAM_INT;
        }
        if ($is_legal !== null) {
            $wheres[] = 'd.is_legal = ?';
            $params[] = $is_legal;
            $types[] = \PDO::PARAM_BOOL;
        }

        if (empty($wheres)) {
            $where = "d.date_creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
            $params = [];
            $types = [];
        } else {
            $where = implode(" AND ", $wheres);
        }

        $extra_select = "";

        switch ($sort) {
            case 'date':
                $order = 'date_creation';
                break;
            case 'likes':
                $order = 'nbvotes';
                break;
            case 'reputation':
                $order = 'reputation';
                break;
            case 'popularity':
            default:
                $order = 'popularity';
                $extra_select = '(d.nbvotes/(1+DATEDIFF(CURRENT_TIMESTAMP(),d.date_creation)/10)) as popularity,';
                break;
        }

        $ctes[] =
            "decklist_results AS (
                SELECT
                    d.id,
                    d.uuid,
                    d.name,
                    d.prettyname,
                    d.date_creation,
                    d.user_id,
                    d.tournament_id,
                    t.description tournament,
                    r.name rotation,
                    $extra_select
                    u.username,
                    u.faction usercolor,
                    u.reputation,
                    u.donation,
                    c.code,
                    c.title identity,
                    c.image_url identity_url,
                    p.name lastpack,
                    d.nbvotes,
                    d.nbfavorites,
                    d.nbcomments
                    from decklist d
                    join user u on d.user_id=u.id
                    join side s on d.side_id=s.id
                    join card c on d.identity_id=c.id
                    join pack p on d.last_pack_id=p.id
                    join faction f on d.faction_id=f.id
                    $join
                    left join tournament t on d.tournament_id=t.id
                    left join rotation r on d.rotation_id=r.id
                    where $where
                    and d.moderation_status in (0,1)
                )";

        $baseQuery = "WITH " . implode(",\n", $ctes);
        // This query isn't as simple as the ones above for the various decklist search entry points, so we can't use getLimitedQueryRowsWithCounts.
        $rows = $dbh->executeQuery("$baseQuery SELECT * FROM decklist_results order by $order desc, name asc limit $start, $limit",
            $params,
            $types
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("$baseQuery SELECT COUNT(*) FROM decklist_results", $params, $types)->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    public function removeConstraints(Decklist $decklist)
    {
        $successors = $this->entityManager->getRepository('AppBundle:Decklist')->findBy(['precedent' => $decklist]);
        foreach ($successors as $successor) {
            /** @var Decklist $successor */
            $successor->setPrecedent(null);
        }

        /** @var Deck[] $children */
        $children = $this->entityManager->getRepository('AppBundle:Deck')->findBy(['parent' => $decklist]);
        foreach ($children as $child) {
            $child->setParent(null);
        }
    }

    public function remove(Decklist $decklist)
    {
        $successors = $this->entityManager->getRepository('AppBundle:Decklist')->findBy(['precedent' => $decklist]);
        foreach ($successors as $successor) {
            /** @var Decklist $successor */
            $successor->setPrecedent(null);
        }

        /** @var Deck[] $children */
        $children = $this->entityManager->getRepository('AppBundle:Deck')->findBy(['parent' => $decklist]);
        foreach ($children as $child) {
            $child->setParent(null);
        }

        $this->entityManager->remove($decklist);
    }

    public function isDecklistLegal(Decklist $decklist)
    {
        // card limits
        $countDql = "SELECT COUNT(DISTINCT s)"
            . " FROM AppBundle:Decklistslot s"
            . " JOIN AppBundle:Card c WITH s.card=c"
            . " WHERE s.quantity>c.deckLimit"
            . " AND s.decklist=?1";
        $countQuery = $this->entityManager->createQuery($countDql)->setParameter(1, $decklist);
        $count = $countQuery->getSingleResult()[1];
        if ($count) {
            return false;
        }

        // card rotation
        $countDql = "SELECT COUNT(DISTINCT s)"
            . " FROM AppBundle:Decklistslot s"
            . " JOIN AppBundle:Card c WITH s.card=c"
            . " JOIN AppBundle:Pack p WITH c.pack=p"
            . " JOIN AppBundle:Cycle y WITH p.cycle=y"
            . " WHERE y.rotated=true"
            . " AND s.decklist=?1";
        $countQuery = $this->entityManager->createQuery($countDql)->setParameter(1, $decklist);
        $count = $countQuery->getSingleResult()[1];
        if ($count) {
            return false;
        }

        // mwl
        $countDql = "SELECT COUNT(DISTINCT l)"
            . " FROM AppBundle:Legality l"
            . " JOIN AppBundle:Mwl m WITH l.mwl=m"
            . " WHERE m.active=true"
            . " AND l.isLegal=false"
            . " AND l.decklist=?1";
        $countQuery = $this->entityManager->createQuery($countDql)->setParameter(1, $decklist);
        $count = $countQuery->getSingleResult()[1];
        if ($count) {
            return false;
        }

        return true;
    }
}

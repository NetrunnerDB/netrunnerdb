<?php

namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Decklist;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DecklistManager
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit",
            [
                $user_id,
            ]
        )
                    ->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
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
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit",
            [
                $user_id,
            ]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of recent decklists with large number of votes
     * @param integer $limit
     * @return array
     */
    public function popular(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by 2*nbvotes/(1+nbjours*nbjours) DESC, nbvotes desc, nbcomments desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of decklists tagged with dotw>0
     * @param integer $limit
     * @return array
     */
    public function dotw(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
    			d.id,
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
    			order by dotw desc
    			limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of decklists with most number of votes
     * @param integer $limit
     * @return array
     */
    public function halloffame(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by nbvotes desc, date_creation desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of decklists with large number of recent comments
     * @param integer $limit
     * @return array
     */
    public function hottopics(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by nbrecentcomments desc, date_creation desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of decklists with a non-null tournament
     * @param integer $limit
     * @return array
     */
    public function tournaments(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of decklists of chosen faction
     * @param integer $limit
     * @return array
     */
    public function faction(string $faction_code, int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit",
            [
                $faction_code,
            ]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of decklists of chosen datapack
     * @param integer $limit
     * @return array
     */
    public function lastpack(string $pack_code, int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit",
            [
                $pack_code,
            ]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of recent decklists
     * @param integer $limit
     * @return array
     */
    public function recent(int $start = 0, int $limit = 30, bool $includeEmptyDesc = true)
    {
        $dbh = $this->entityManager->getConnection();

        $additional_clause = $includeEmptyDesc ? "" : "and d.rawdescription!=''";

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of trashed decklists
     * @param integer $limit
     * @return array
     */
    public function trashed(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
        ];
    }

    /**
     * returns the list of restored decklists
     * @param integer $limit
     * @return array
     */
    public function restored(int $start = 0, int $limit = 30)
    {
        $dbh = $this->entityManager->getConnection();

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                order by date_creation desc
                limit $start, $limit"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return [
            "count"     => $count,
            "decklists" => $rows,
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
            $packs = $dbh->executeQuery("SELECT id FROM pack")->fetchAll(\PDO::FETCH_COLUMN);
        }

        if ($faction_code === "corp" || $faction_code === "runner") {
            $side_code = $faction_code;
            unset($faction_code);
        }

        $joins = [];
        $wheres = [];
        $params = [];
        $types = [];

        $cards = $cardRepository->findBy(['code' => $cards_code]);

        foreach ($cards as $card) {
            $joins[] = '?';
            $params[] = $card->getId();
            $types[] = \PDO::PARAM_STR;

            $packs[] = $card->getPack()->getId();
        }

        $join = '';
        $group_by = '';
        $group_by_count = 0;
        if (count($joins)) {
            $join = ' JOIN decklistslot dls'
                . ' ON dls.decklist_id=d.id'
                . ' AND dls.card_id IN'
                . ' (' . implode(',', $joins) . ')';
            $group_by_count = count($joins);
            $group_by = ' GROUP BY dls.decklist_id'
                . " HAVING COUNT(DISTINCT dls.card_id) = $group_by_count";
        }

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

        if (count($packs)) {
            $wheres[] = 'not exists(select * from decklistslot join card on decklistslot.card_id=card.id where decklistslot.decklist_id=d.id and card.pack_id not in (?))';
            $params[] = array_unique($packs);
            $types[] = Connection::PARAM_INT_ARRAY;
        }
        if (!empty($mwl_code)) {
            $wheres[] = 'exists(select * from legality join mwl on legality.mwl_id=mwl.id where legality.decklist_id=d.id and mwl.code=? and legality.is_legal=1)';
            $params[] = $mwl_code;
            $types[] = \PDO::PARAM_INT;
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

        $rows = $dbh->executeQuery(
            "SELECT SQL_CALC_FOUND_ROWS
                d.id,
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
                $group_by
                order by $order desc
                limit $start, $limit",
            $params,
            $types
        )->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

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

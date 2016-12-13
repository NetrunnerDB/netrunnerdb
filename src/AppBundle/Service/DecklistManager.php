<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Decklist;
use Symfony\Component\HttpFoundation\Request;

class DecklistManager
{

    public function __construct (EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * returns the list of decklist favorited by user
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function favorites ($user_id, $start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
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
                where f.user_id=?
                and d.moderation_status<2
                order by date_creation desc
                limit $start, $limit", array(
                    $user_id
                ))
                ->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists published by user
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function by_author ($user_id, $start = 0, $limit = 30)
    {

        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where d.user_id=?
                and d.moderation_status<3
                order by date_creation desc
                limit $start, $limit", array(
                    $user_id
                ))->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of recent decklists with large number of votes
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function popular ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
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
                where d.date_creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                and d.moderation_status<2
                order by 2*nbvotes/(1+nbjours*nbjours) DESC, nbvotes desc, nbcomments desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists tagged with dotw>0
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function dotw ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
    			d.id,
    			d.name,
    			d.prettyname,
    			d.date_creation,
    			d.user_id,
    			d.tournament_id,
    			t.description tournament,
    			u.username,
    			u.faction usercolor,
    			u.reputation,
    			u.donation,
    			c.code,
    			c.title identity,
                p.name lastpack,
    			d.nbvotes,
    			d.nbfavorites,
    			d.nbcomments
    			from decklist d
    			join user u on d.user_id=u.id
    			join card c on d.identity_id=c.id
    			join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
    			where dotw > 0
    			order by dotw desc
    			limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists with most number of votes
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function halloffame ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where nbvotes > 10
                and d.moderation_status<2
                order by nbvotes desc, date_creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists with large number of recent comments
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function hottopics ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
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
                where d.nbcomments > 1
                and d.moderation_status<2
                order by nbrecentcomments desc, date_creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists with a non-null tournament
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function tournaments ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where d.tournament_id is not null
                and d.moderation_status<2
                order by date_creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists of chosen faction
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function faction ($faction_code, $start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
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
                where f.code=?
                and d.moderation_status<2
                order by date_creation desc
                limit $start, $limit", array(
                    $faction_code
                ))->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of decklists of chosen datapack
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function lastpack ($pack_code, $start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where p.code=?
                and d.moderation_status<2
                order by date_creation desc
                limit $start, $limit", array(
                    $pack_code
                ))->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    /**
     * returns the list of recent decklists
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function recent ($start = 0, $limit = 30, $includeEmptyDesc = true)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

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
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where d.date_creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                and d.moderation_status<2
                $additional_clause
                order by date_creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }
    
    /**
     * returns the list of trashed decklists
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function trashed ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where d.moderation_status=2
                order by date_creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }
    
    /**
     * returns the list of restored decklists
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function restored ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $rows = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.date_creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where d.moderation_status=1
                order by date_creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }
    
    /**
     * returns a list of decklists according to search criteria
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function find ($start = 0, $limit = 30, Request $request)
    {

        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $cardRepository = $this->doctrine->getRepository('AppBundle:Card');

        $cards_code = $request->query->get('cards');
        if(!is_array($cards_code)) {
            $cards_code = array();
        }
        $faction_code = filter_var($request->query->get('faction'), FILTER_SANITIZE_STRING);
        $author_name = filter_var($request->query->get('author'), FILTER_SANITIZE_STRING);
        $decklist_title = filter_var($request->query->get('title'), FILTER_SANITIZE_STRING);
        $sort = $request->query->get('sort');
        $packs = $request->query->get('packs');
        $mwl_code = $request->query->get('mwl_code');

        if(!is_array($packs)) {
            $packs = $dbh->executeQuery("select id from pack")->fetchAll(\PDO::FETCH_COLUMN);
        }

        if($faction_code === "corp" || $faction_code === "runner") {
            $side_code = $faction_code;
            unset($faction_code);
        }

        $wheres = array();
        $params = array();
        $types = array();
        if(!empty($side_code)) {
            $wheres[] = 's.code=?';
            $params[] = $side_code;
            $types[] = \PDO::PARAM_STR;
        }
        if(!empty($faction_code)) {
            $wheres[] = 'f.code=?';
            $params[] = $faction_code;
            $types[] = \PDO::PARAM_STR;
        }
        if(!empty($author_name)) {
            $wheres[] = 'u.username=?';
            $params[] = $author_name;
            $types[] = \PDO::PARAM_STR;
        }
        if(!empty($decklist_title)) {
            $wheres[] = 'd.name like ?';
            $params[] = '%' . $decklist_title . '%';
            $types[] = \PDO::PARAM_STR;
        }
        if(count($cards_code)) {
            foreach($cards_code as $card_code) {
                /* @var $card \AppBundle\Entity\Card */
                $card = $cardRepository->findOneBy(array('code' => $card_code));
                if(!$card)
                    continue;

                $wheres[] = 'exists(select * from decklistslot where decklistslot.decklist_id=d.id and decklistslot.card_id=?)';
                $params[] = $card->getId();
                $types[] = \PDO::PARAM_STR;

                $packs[] = $card->getPack()->getId();
            }
        }
        if(count($packs)) {
            $wheres[] = 'not exists(select * from decklistslot join card on decklistslot.card_id=card.id where decklistslot.decklist_id=d.id and card.pack_id not in (?))';
            $params[] = array_unique($packs);
            $types[] = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
        }
        if(!empty($mwl_code)) {
            $wheres[] = 'exists(select * from legality join mwl on legality.mwl_id=mwl.id where legality.decklist_id=d.id and mwl.code=? and legality.is_legal=1)';
            $params[] = $mwl_code;
            $types[] = \PDO::PARAM_INT;
        }

        if(empty($wheres)) {
            $where = "d.date_creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
            $params = array();
            $types = array();
        } else {
            $where = implode(" AND ", $wheres);
        }

        $extra_select = "";

        switch($sort) {
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
                $extra_select
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
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
                left join tournament t on d.tournament_id=t.id
                where $where
                and d.moderation_status<2
                order by $order desc
                limit $start, $limit", $params, $types)->fetchAll(\PDO::FETCH_ASSOC);

        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        return array(
            "count" => $count,
            "decklists" => $rows
        );
    }

    public function removeConstraints (Decklist $decklist)
    {
        $successors = $this->doctrine->getRepository('AppBundle:Decklist')->findBy(array('precedent' => $decklist));
        foreach($successors as $successor) {
            /* @var $successor \AppBundle\Entity\Decklist */
            $successor->setPrecedent(null);
        }

        $children = $this->doctrine->getRepository('AppBundle:Deck')->findBy(array('parent' => $decklist));
        foreach($children as $child) {
            /* @var $child \AppBundle\Entity\Deck */
            $child->setParent(null);
        }
    }

    public function remove (Decklist $decklist)
    {
        $successors = $this->doctrine->getRepository('AppBundle:Decklist')->findBy(array('precedent' => $decklist));
        foreach($successors as $successor) {
            /* @var $successor \AppBundle\Entity\Decklist */
            $successor->setPrecedent(null);
        }

        $children = $this->doctrine->getRepository('AppBundle:Deck')->findBy(array('parent' => $decklist));
        foreach($children as $child) {
            /* @var $child \AppBundle\Entity\Deck */
            $child->setParent(null);
        }

        $this->doctrine->remove($decklist);
    }

}

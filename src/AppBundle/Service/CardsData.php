<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
 *
 */

class CardsData {

    public static $faction_letters = [
        'haas-bioroid' => 'h',
        'weyland-consortium' => 'w',
        'anarch' => 'a',
        'shaper' => 's',
        'criminal' => 'c',
        'jinteki' => 'j',
        'nbn' => 'n',
        'neutral-corp' => '-',
        'neutral-runner' => '-',
        'apex' => 'p',
        'adam' => 'd',
        'sunny-lebeau' => 'u',
    ];

    /* @var Registry */
    private $doctrine;

    /* @var RequestStack */
    private $request_stack;

    /* @var Router */
    private $router;

    public function __construct(Registry $doctrine, RequestStack $request_stack, Router $router) {
        $this->doctrine = $doctrine;
        $this->request_stack = $request_stack;
        $this->router = $router;
    }

    private function backwardCompatibilitySymbols($text) {
        $map = array(
            '[subroutine]' => '[Subroutine]',
            '[credit]' => '[Credits]',
            '[trash]' => '[Trash]',
            '[click]' => '[Click]',
            '[recurring-credit]' => '[Recurring Credits]',
            '[mu]' => '[Memory Unit]',
            '[link]' => '[Link]'
        );

        return str_replace(array_keys($map), array_values($map), $text);
    }

    /**
     * Searches for and replaces symbol tokens with markup in a given text.
     * @param string $text
     * @return string
     */
    public function replaceSymbols($text) {
        $map = array(
            '[subroutine]' => '<span class="icon icon-subroutine"></span>',
            '[credit]' => '<span class="icon icon-credit"></span>',
            '[trash]' => '<span class="icon icon-trash"></span>',
            '[click]' => '<span class="icon icon-click"></span>',
            '[recurring-credit]' => '<span class="icon icon-recurring-credit"></span>',
            '[mu]' => '<span class="icon icon-mu"></span>',
            '[link]' => '<span class="icon icon-link"></span>',
            '[anarch]' => '<span class="icon icon-anarch"></span>',
            '[criminal]' => '<span class="icon icon-criminal"></span>',
            '[shaper]' => '<span class="icon icon-shaper"></span>',
            '[jinteki]' => '<span class="icon icon-jinteki"></span>',
            '[haas-bioroid]' => '<span class="icon icon-haas-bioroid"></span>',
            '[nbn]' => '<span class="icon icon-nbn"></span>',
            '[weyland-consortium]' => '<span class="icon icon-weyland-consortium"></span>',
        );

        return str_replace(array_keys($map), array_values($map), $text);
    }

    public function allsetsnocycledata() {
        $list_packs = $this->doctrine->getRepository('AppBundle:Pack')->findBy(array(), array("dateRelease" => "ASC", "position" => "ASC"));
        $packs = array();
        foreach ($list_packs as $pack) {
            $real = count($pack->getCards());
            $max = $pack->getSize();
            $packs[] = array(
                "name" => $pack->getName(),
                "code" => $pack->getCode(),
                "number" => $pack->getPosition(),
                "available" => $pack->getDateRelease() ? $pack->getDateRelease()->format('Y-m-d') : '',
                "known" => intval($real),
                "total" => $max,
                "url" => $this->router->generate('cards_list', array('pack_code' => $pack->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
            );
        }
        return $packs;
    }

    public function allsetsdata() {
        $list_cycles = $this->doctrine->getRepository('AppBundle:Cycle')->findBy(array(), array("position" => "ASC"));
        $cycles = array();
        foreach ($list_cycles as $cycle) {
            $packs = array();
            $sreal = 0;
            $smax = 0;
            foreach ($cycle->getPacks() as $pack) {
                $real = count($pack->getCards());
                $sreal += $real;
                $max = $pack->getSize();
                $smax += $max;
                $packs[] = array(
                    "name" => $pack->getName(),
                    "code" => $pack->getCode(),
                    "available" => $pack->getDateRelease() ? $pack->getDateRelease()->format('Y-m-d') : '',
                    "known" => intval($real),
                    "total" => $max,
                    "url" => $this->router->generate('cards_list', array('pack_code' => $pack->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
                    "search" => "e:" . $pack->getCode()
                );
            }
            if ($cycle->getSize() === 1) {
                $cycles[] = $packs[0];
            } else {
                $cycles[] = array(
                    "name" => $cycle->getName(),
                    "code" => $cycle->getCode(),
                    "known" => intval($sreal),
                    "total" => $smax,
                    "url" => $this->router->generate('cards_cycle', array('cycle_code' => $cycle->getCode()), UrlGeneratorInterface::ABSOLUTE_URL),
                    "search" => 'c:' . $cycle->getCode(),
                    "packs" => $packs,
                );
            }
        }
        return $cycles;
    }

    public function get_search_rows($conditions, $sortorder, $forceempty = false) {
        $locale = $this->request_stack->getCurrentRequest()->getLocale();

        $i = 0;

        // construction de la requete sql
        $qb = $this->doctrine->getRepository('AppBundle:Card')->createQueryBuilder('c');
        $qb->select('c', 'p', 'y', 't', 'f', 's');
        $qb->leftJoin('c.pack', 'p')
                ->leftJoin('p.cycle', 'y')
                ->leftJoin('c.type', 't')
                ->leftJoin('c.faction', 'f')
                ->leftJoin('c.side', 's');
        $qb2 = null;
        $qb3 = null;

        $clauses = [];
        $parameters = [];

        foreach ($conditions as $condition) {
            $type = array_shift($condition);
            $operator = array_shift($condition);
            switch ($type) {
                case '': // title or index
                    $or = array();
                    foreach ($condition as $arg) {
                        $code = preg_match('/^\d\d\d\d\d$/u', $arg);
                        $acronym = preg_match('/^[A-Z]{2,}$/', $arg);
                        if ($code) {
                            $or[] = "(c.code = ?$i)";
                            $parameters[$i++] = $arg;
                        } else if ($acronym) {
                            $or[] = "(BINARY(c.title) like ?$i)";
                            $parameters[$i++] = "%$arg%";
                            $like = implode('% ', str_split($arg));
                            $or[] = "(REPLACE(c.title, '-', ' ') like ?$i)";
                            $parameters[$i++] = "$like%";
                        } else {
                            if ($arg == 'Franklin')
                                $arg = 'Crick'; // easter egg
                            $or[] = "(c.title like ?$i)";
                            $parameters[$i++] = "%$arg%";
                        }
                    }
                    $clauses[] = implode(" or ", $or);
                    break;
                case 'x': // text
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.text like ?$i)";
                                break;
                            case '!': $or[] = "(c.text not like ?$i)";
                                break;
                        }
                        $parameters[$i++] = "%$arg%";
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'a': // flavor
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.flavor like ?$i)";
                                break;
                            case '!': $or[] = "(c.flavor not like ?$i)";
                                break;
                        }
                        $parameters[$i++] = "%$arg%";
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'e': // extension (pack)
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(p.code = ?$i)";
                                break;
                            case '!': $or[] = "(p.code != ?$i)";
                                break;
                            case '<':
                                if (!isset($qb2)) {
                                    $qb2 = $this->doctrine->getRepository('AppBundle:Pack')->createQueryBuilder('p2');
                                    $or[] = $qb->expr()->lt('p.dateRelease', '(' . $qb2->select('p2.dateRelease')->where("p2.code = ?$i")->getDql() . ')');
                                }
                                break;
                            case '>':
                                if (!isset($qb3)) {
                                    $qb3 = $this->doctrine->getRepository('AppBundle:Pack')->createQueryBuilder('p3');
                                    $or[] = $qb->expr()->gt('p.dateRelease', '(' . $qb3->select('p3.dateRelease')->where("p3.code = ?$i")->getDql() . ')');
                                }
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'c': // cycle (cycle)
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(y.position = ?$i)";
                                break;
                            case '!': $or[] = "(y.position != ?$i)";
                                break;
                            case '<': $or[] = "(y.position < ?$i)";
                                break;
                            case '>': $or[] = "(y.position > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 't': // type
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(t.code = ?$i)";
                                break;
                            case '!': $or[] = "(t.code != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'f': // faction
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(f.code = ?$i)";
                                break;
                            case '!': $or[] = "(f.code != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 's': // subtype (keywords)
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "((c.keywords = ?$i) or (c.keywords like ?" . ($i + 1) . ") or (c.keywords like ?" . ($i + 2) . ") or (c.keywords like ?" . ($i + 3) . "))";
                                $parameters[$i++] = "$arg";
                                $parameters[$i++] = "$arg %";
                                $parameters[$i++] = "% $arg";
                                $parameters[$i++] = "% $arg %";
                                break;
                            case '!':
                                $or[] = "(c.keywords is null or ((c.keywords != ?$i) and (c.keywords not like ?" . ($i + 1) . ") and (c.keywords not like ?" . ($i + 2) . ") and (c.keywords not like ?" . ($i + 3) . ")))";
                                $parameters[$i++] = "$arg";
                                $parameters[$i++] = "$arg %";
                                $parameters[$i++] = "% $arg";
                                $parameters[$i++] = "% $arg %";
                                break;
                        }
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'd': // side
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(SUBSTRING(s.code,1,1) = ?$i)";
                                break;
                            case '!': $or[] = "(SUBSTRING(s.code,1,1) != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'i': // illustrator
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.illustrator = ?$i)";
                                break;
                            case '!': $or[] = "(c.illustrator != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'o': // cost
                    $or = array();
                    foreach ($condition as $arg) {
                        if ($arg === 'X') {
                            switch ($operator) {
                                case ':': $or[] = "(c.cost is null)";
                                    break;
                                case '!': $or[] = "(c.cost is not null)";
                                    break;
                            }
                        } else {
                            switch ($operator) {
                                case ':': $or[] = "(c.cost = ?$i)";
                                    break;
                                case '!': $or[] = "(c.cost != ?$i)";
                                    break;
                                case '<': $or[] = "(c.cost < ?$i)";
                                    break;
                                case '>': $or[] = "(c.cost > ?$i)";
                                    break;
                            }
                            $parameters[$i++] = $arg;
                        }
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'g': // advancementcost
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.advancementCost = ?$i)";
                                break;
                            case '!': $or[] = "(c.advancementCost != ?$i)";
                                break;
                            case '<': $or[] = "(c.advancementCost < ?$i)";
                                break;
                            case '>': $or[] = "(c.advancementCost > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'm': // memoryunits
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.memoryCost = ?$i)";
                                break;
                            case '!': $or[] = "(c.memoryCost != ?$i)";
                                break;
                            case '<': $or[] = "(c.memoryCost < ?$i)";
                                break;
                            case '>': $or[] = "(c.memoryCost > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'n': // influence or influenceLimit
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.factionCost = ?$i or c.influenceLimit =?$i)";
                                break;
                            case '!': $or[] = "(c.factionCost != ?$i or c.influenceLimit != ?$i)";
                                break;
                            case '<': $or[] = "(c.factionCost < ?$i or c.influenceLimit < ?$i)";
                                break;
                            case '>': $or[] = "(c.factionCost > ?$i or c.influenceLimit > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'p': // strength
                    $or = array();
                    foreach ($condition as $arg) {
                        if ($arg === 'X') {
                            switch ($operator) {
                                case ':': $or[] = "(c.strength is null)";
                                    break;
                                case '!': $or[] = "(c.strength is not null)";
                                    break;
                            }
                        } else {
                            switch ($operator) {
                                case ':': $or[] = "(c.strength = ?$i)";
                                    break;
                                case '!': $or[] = "(c.strength != ?$i)";
                                    break;
                                case '<': $or[] = "(c.strength < ?$i)";
                                    break;
                                case '>': $or[] = "(c.strength > ?$i)";
                                    break;
                            }
                            $parameters[$i++] = $arg;
                        }
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'v': // agendapoints
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.agendaPoints = ?$i)";
                                break;
                            case '!': $or[] = "(c.agendaPoints != ?$i)";
                                break;
                            case '<': $or[] = "(c.agendaPoints < ?$i)";
                                break;
                            case '>': $or[] = "(c.agendaPoints > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'h': // trashcost
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.trashCost = ?$i)";
                                break;
                            case '!': $or[] = "(c.trashCost != ?$i)";
                                break;
                            case '<': $or[] = "(c.trashCost < ?$i)";
                                break;
                            case '>': $or[] = "(c.trashCost > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'y': // quantity
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':': $or[] = "(c.quantity = ?$i)";
                                break;
                            case '!': $or[] = "(c.quantity != ?$i)";
                                break;
                            case '<': $or[] = "(c.quantity < ?$i)";
                                break;
                            case '>': $or[] = "(c.quantity > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'r': // release
                    $or = array();
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case '<': $or[] = "(p.dateRelease <= ?$i)";
                                break;
                            case '>': $or[] = "(p.dateRelease > ?$i or p.dateRelease IS NULL)";
                                break;
                        }
                        if ($arg == "now")
                            $parameters[$i++] = new \DateTime();
                        else
                            $parameters[$i++] = new \DateTime($arg);
                    }
                    $clauses[] = implode(" or ", $or);
                    break;
                case 'u': // unique
                    if (($operator == ':' && $condition[0]) || ($operator == '!' && !$condition[0])) {
                        $clauses[] = "(c.uniqueness = 1)";
                    } else {
                        $clauses[] = "(c.uniqueness = 0)";
                    }
                    $i++;
                    break;
            }
        }

        if (count($clauses) === 0 && !$forceempty) {
            return;
        }

        foreach ($clauses as $clause) {
            $qb->andWhere($clause);
        }
        foreach ($parameters as $index => $parameter) {
            $qb->setParameter($index, $parameter);
        }

        switch ($sortorder) {
            case 'set': $qb->orderBy('c.code');
                break;
            case 'name': $qb->orderBy('c.title');
                break;
            case 'faction': $qb->orderBy('c.side', 'DESC')->addOrderBy('c.faction')->addOrderBy('c.type');
                break;
            case 'type': $qb->orderBy('c.side', 'DESC')->addOrderBy('c.type')->addOrderBy('c.faction');
                break;
            case 'cost': $qb->orderBy('c.type')->addOrderBy('c.cost')->addOrderBy('c.advancementCost');
                break;
            case 'strength': $qb->orderBy('c.type')->addOrderBy('c.strength')->addOrderBy('c.agendaPoints')->addOrderBy('c.trashCost');
                break;
        }
        $query = $qb->getQuery();

        $query->setHint(
                \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale
        );
        $query->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1
        );

        $rows = $query->getResult();

        return $rows;
    }

    /**
     *
     * @param \AppBundle\Entity\Card $card
     * @return multitype:multitype: string number mixed NULL unknown
     */
    public function getCardInfo($card) {
        static $cache = array();

        $locale = $this->request_stack->getCurrentRequest()->getLocale();

        if (isset($cache[$card->getId()]) && isset($cache[$card->getId()][$locale])) {
            return $cache[$card->getId()][$locale];
        }

        $cardinfo = array(
            "id" => $card->getId(),
            "code" => $card->getCode(),
            "title" => $card->getTitle(),
            "type_name" => $card->getType()->getName(),
            "type_code" => $card->getType()->getCode(),
            "subtype" => $card->getKeywords(),
            "text" => $card->getText(),
            "advancementcost" => $card->getAdvancementCost(),
            "agendapoints" => $card->getAgendaPoints(),
            "baselink" => $card->getBaseLink(),
            "cost" => $card->getCost(),
            "faction_name" => $card->getFaction()->getName(),
            "faction_code" => $card->getFaction()->getCode(),
            "factioncost" => $card->getFactionCost(),
            "flavor" => $card->getFlavor(),
            "illustrator" => $card->getIllustrator(),
            "influencelimit" => $card->getInfluenceLimit(),
            "memoryunits" => $card->getMemoryCost(),
            "minimumdecksize" => $card->getMinimumDeckSize(),
            "position" => $card->getPosition(),
            "quantity" => $card->getQuantity(),
            "pack_name" => $card->getPack()->getName(),
            "pack_code" => $card->getPack()->getCode(),
            "side_name" => $card->getSide()->getName(),
            "side_code" => $card->getSide()->getCode(),
            "strength" => $card->getStrength(),
            "trash" => $card->getTrashCost(),
            "uniqueness" => $card->getUniqueness(),
            "limited" => $card->getDeckLimit(),
            "cycle_name" => $card->getPack()->getCycle()->getName(),
            "cycle_code" => $card->getPack()->getCycle()->getCode(),
            "ancur_link" => $card->getAncurLink(),
        );

        // setting the card cost to X if the cost is null and the card is not of a costless type
        if ($cardinfo['cost'] === null && !in_array($cardinfo['type_code'], ['agenda', 'identity'])) {
            $cardinfo['cost'] = 'X';
        }

        // setting the card strength to X if the strength is null and the card subtype has icebreaker
        if ($cardinfo['strength'] === null && strstr($cardinfo['subtype'], 'Icebreaker') !== FALSE) {
            $cardinfo['strength'] = 'X';
        }

        $cardinfo['url'] = $this->router->generate('cards_zoom', array('card_code' => $card->getCode(), '_locale' => $locale), UrlGeneratorInterface::ABSOLUTE_URL);
        $cardinfo['imageUrl'] = $this->request_stack->getCurrentRequest()->getSchemeAndHttpHost() . "/card_image/" . $card->getCode() . ".png";

        // replacing <trace>
        $cardinfo['text'] = preg_replace('/<trace>([^<]+) ([X\d]+)<\/trace>/', '<strong>\1<sup>\2</sup></strong>–', $cardinfo['text']);

        // replacing <errata>
        $cardinfo['text'] = preg_replace('/<errata>(.+)<\/errata>/', '<em><span class="glyphicon glyphicon-alert"></span> \1</em>', $cardinfo['text']);

        // replacing <champion>
        $cardinfo['flavor'] = preg_replace('/<champion>(.+)<\/champion>/', '<span class="champion">\1</champion>', $cardinfo['flavor']);

        $cardinfo['text'] = $this->replaceSymbols($cardinfo['text']);
        $cardinfo['text'] = str_replace('&', '&amp;', $cardinfo['text']);
        $cardinfo['text'] = implode(array_map(function ($l) {
                    return "<p>$l</p>";
                }, explode("\n", $cardinfo['text'])));
        $cardinfo['flavor'] = $this->replaceSymbols($cardinfo['flavor']);
        $cardinfo['flavor'] = str_replace('&', '&amp;', $cardinfo['flavor']);
        $cardinfo['cssfaction'] = str_replace(" ", "-", mb_strtolower($card->getFaction()->getName()));

        $cache[$card->getId()][$locale] = $cardinfo;

        return $cardinfo;
    }

    public function syntax($query) {
        // renvoie une liste de conditions (array)
        // chaque condition est un tableau à n>1 éléments
        // le premier est le type de condition (0 ou 1 caractère)
        // les suivants sont les arguments, en OR

        $query = preg_replace('/\s+/u', ' ', trim($query));

        $list = array();
        $cond = null;
        // l'automate a 3 états :
        // 1:recherche de type
        // 2:recherche d'argument principal
        // 3:recherche d'argument supplémentaire
        // 4:erreur de parsing, on recherche la prochaine condition
        // s'il tombe sur un argument alors qu'il est en recherche de type, alors le type est vide
        $etat = 1;
        while ($query != "") {
            if ($etat == 1) {
                if (isset($cond) && $etat != 4 && count($cond) > 2) {
                    $list[] = $cond;
                }
                // on commence par rechercher un type de condition
                $match = array();
                if (preg_match('/^(\p{L})([:<>!])(.*)/u', $query, $match)) { // jeton "condition:"
                    $cond = array(mb_strtolower($match[1]), $match[2]);
                    $query = $match[3];
                } else {
                    $cond = array("", ":");
                }
                $etat = 2;
            } else {
                if (preg_match('/^"([^"]*)"(.*)/u', $query, $match) // jeton "texte libre entre guillements"
                        || preg_match('/^([\p{L}\p{N}\-\&\.\!\'\;]+)(.*)/u', $query, $match) // jeton "texte autorisé sans guillements"
                ) {
                    if (($etat == 2 && count($cond) == 2) || $etat == 3) {
                        $cond[] = $match[1];
                        $query = $match[2];
                        $etat = 2;
                    } else {
                        // erreur
                        $query = $match[2];
                        $etat = 4;
                    }
                } else if (preg_match('/^\|(.*)/u', $query, $match)) { // jeton "|"
                    if (($cond[1] == ':' || $cond[1] == '!') && (($etat == 2 && count($cond) > 2) || $etat == 3)) {
                        $query = $match[1];
                        $etat = 3;
                    } else {
                        // erreur
                        $query = $match[1];
                        $etat = 4;
                    }
                } else if (preg_match('/^ (.*)/u', $query, $match)) { // jeton " "
                    $query = $match[1];
                    $etat = 1;
                } else {
                    // erreur
                    $query = substr($query, 1);
                    $etat = 4;
                }
            }
        }
        if (isset($cond) && $etat != 4 && count($cond) > 2) {
            $list[] = $cond;
        }
        return $list;
    }

    public function validateConditions(&$conditions) {
        // suppression des conditions invalides
        $canDoNumeric = array('o', 'n', 'p', 'r', 'y', 'e', 'h');
        $numeric = array('<', '>');
        foreach ($conditions as $i => $l) {
            if (in_array($l[1], $numeric) && !in_array($l[0], $canDoNumeric))
                unset($conditions[$i]);
            if ($l[0] == 'f' && strlen($l[2]) === 1) {
                $keys = array_keys(self::$faction_letters, $l[2]);
                unset($conditions[$i]);
                if (count($keys)) {
                    array_unshift($keys, 'f', ':');
                    array_unshift($conditions, $keys);
                }
            }
        }
    }

    public function buildQueryFromConditions($conditions) {
        // reconstruction de la bonne chaine de recherche pour affichage
        return implode(" ", array_map(
                        function ($l) {
                    return ($l[0] ? $l[0] . $l[1] : "")
                            . implode("|", array_map(
                                            function ($s) {
                                        return preg_match("/^[\p{L}\p{N}\-\&\.\!\'\;]+$/u", $s) ? $s : "\"$s\"";
                                    }, array_slice($l, 2)
                    ));
                }, $conditions
        ));
    }

    public function get_reviews($card) {
        $reviews = $this->doctrine->getRepository('AppBundle:Review')->findBy(array('card' => $card), array('nbvotes' => 'DESC'));

        $response = array();
        foreach ($reviews as $review) {
            /* @var $review \AppBundle\Entity\Review */
            $user = $review->getUser();
            $date_creation = $review->getDatecreation();
            $response[] = array(
                'id' => $review->getId(),
                'text' => $review->getText(),
                'author_id' => $user->getId(),
                'author_name' => $user->getUsername(),
                'author_reputation' => $user->getReputation(),
                'author_donation' => $user->getDonation(),
                'author_color' => $user->getFaction(),
                'date_creation' => $date_creation,
                'nbvotes' => $review->getNbvotes(),
                'comments' => $review->getComments(),
            );
        }

        return $response;
    }

    /**
     * Searches a Identity card by its partial title
     * @return \AppBundle\Entity\Card
     */
    public function find_identity($partialTitle) {
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb->select('c')->from('AppBundle:Card', 'c')->join('AppBundle:Type', 't', 'WITH', 'c.type = t');
        $qb->where($qb->expr()->eq('t.name', ':typeName'));
        $qb->andWhere($qb->expr()->like('c.title', ':title'));
        $query = $qb->getQuery();
        $query->setParameter('typeName', 'Identity');
        $query->setParameter('title', '%' . $partialTitle . '%');
        $card = $query->getSingleResult();
        return $card;
    }

}

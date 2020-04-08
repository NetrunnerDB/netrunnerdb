<?php

namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Cycle;
use AppBundle\Entity\Mwl;
use AppBundle\Entity\Pack;
use AppBundle\Entity\Review;
use AppBundle\Entity\Ruling;
use AppBundle\Entity\Rotation;
use AppBundle\Service\RotationService;
use AppBundle\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CardsData
{
    public static $faction_letters = [
        'haas-bioroid'       => 'h',
        'weyland-consortium' => 'w',
        'anarch'             => 'a',
        'shaper'             => 's',
        'criminal'           => 'c',
        'jinteki'            => 'j',
        'nbn'                => 'n',
        'neutral-corp'       => '-',
        'neutral-runner'     => '-',
        'apex'               => 'p',
        'adam'               => 'd',
        'sunny-lebeau'       => 'u',
    ];

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var PackRepository $packRepository */
    private $packRepository;

    /** @var RouterInterface $router */
    private $router;

    /** @var Packages $packages */
    private $packages;

    public function __construct(
        EntityManagerInterface $entityManager,
        RepositoryFactory $repositoryFactory,
        RouterInterface $router,
        Packages $packages
    ) {
        $this->entityManager = $entityManager;
        $this->packRepository = $repositoryFactory->getPackRepository();
        $this->router = $router;
        $this->packages = $packages;
    }

    /**
     * Searches for and replaces symbol tokens with markup in a given text.
     * @param string $text
     * @return string
     */
    public function replaceSymbols(string $text)
    {
        $map = [
            '[subroutine]'         => '<span class="icon icon-subroutine" aria-hidden="true"></span><span class="icon-fallback">subroutine</span>',
            '[credit]'             => '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span>',
            '[trash]'              => '<span class="icon icon-trash" aria-hidden="true"></span><span class="icon-fallback">trash</span>',
            '[click]'              => '<span class="icon icon-click" aria-hidden="true"></span><span class="icon-fallback">click</span>',
            '[recurring-credit]'   => '<span class="icon icon-recurring-credit" aria-hidden="true"></span><span class="icon-fallback">recurring credit</span>',
            '[mu]'                 => '<span class="icon icon-mu" aria-hidden="true"></span><span class="icon-fallback">memory unit</span>',
            '[link]'               => '<span class="icon icon-link" aria-hidden="true"></span><span class="icon-fallback">link</span>',
            '[anarch]'             => '<span class="icon icon-anarch" aria-hidden="true"></span><span class="icon-fallback">anarch</span>',
            '[criminal]'           => '<span class="icon icon-criminal" aria-hidden="true"></span><span class="icon-fallback">criminal</span>',
            '[shaper]'             => '<span class="icon icon-shaper" aria-hidden="true"></span><span class="icon-fallback">shaper</span>',
            '[jinteki]'            => '<span class="icon icon-jinteki" aria-hidden="true"></span><span class="icon-fallback">jinteki</span>',
            '[haas-bioroid]'       => '<span class="icon icon-haas-bioroid" aria-hidden="true"></span><span class="icon-fallback">haas bioroid</span>',
            '[nbn]'                => '<span class="icon icon-nbn" aria-hidden="true"></span><span class="icon-fallback">nbn</span>',
            '[weyland-consortium]' => '<span class="icon icon-weyland-consortium" aria-hidden="true"></span><span class="icon-fallback">weyland consortium</span>',
            '[interrupt]'          => '<span class="icon icon-interrupt" aria-hidden="true"></span><span class="icon-fallback">interrupt</span>',
        ];

        return str_replace(array_keys($map), array_values($map), $text);
    }

    public function allsetsnocycledata()
    {
        $list_packs = $this->packRepository->findBy([], ["dateRelease" => "ASC", "position" => "ASC"]);
        $packs = [];
        foreach ($list_packs as $pack) {
            $real = $pack->getCards()->count();
            $max = $pack->getSize();
            $packs[] = [
                "name"      => $pack->getName(),
                "code"      => $pack->getCode(),
                "number"    => $pack->getPosition(),
                "available" => $pack->getDateRelease() ? $pack->getDateRelease()->format('Y-m-d') : '',
                "known"     => intval($real),
                "total"     => $max,
                "url"       => $this->router->generate('cards_list', ['pack_code' => $pack->getCode()], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
        }

        return $packs;
    }

    public function allsetsdata()
    {
        /** @var Cycle[] $list_cycles */
        $list_cycles = $this->entityManager->getRepository(Cycle::class)->findBy([], ["position" => "ASC"]);
        $cycles = [];
        foreach ($list_cycles as $cycle) {
            $packs = [];
            $sreal = 0;
            $smax = 0;

            foreach ($this->packRepository->findByCycleWithCardCount($cycle) as $pack) {
                $sreal += $pack->getCardCount();
                $max = $pack->getSize();
                $smax += $max;
                $packs[] = [
                    "name"      => $pack->getName(),
                    "code"      => $pack->getCode(),
                    "available" => $pack->getDateRelease() ? $pack->getDateRelease()->format('Y-m-d') : '',
                    "known"     => $pack->getCardCount(),
                    "total"     => $max,
                    "url"       => $this->router->generate('cards_list', ['pack_code' => $pack->getCode()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "search"    => "e:" . $pack->getCode(),
                    "icon"      => $pack->getCycle()->getCode(),
                ];
            }

            if ($cycle->getSize() === 1) {
                $cycles[] = $packs[0];
            } else {
                $cycles[] = [
                    "name"   => $cycle->getName(),
                    "code"   => $cycle->getCode(),
                    "available"  => $packs[0]["available"],
                    "known"  => intval($sreal),
                    "total"  => $smax,
                    "url"    => $this->router->generate('cards_cycle', ['cycle_code' => $cycle->getCode()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "search" => 'c:' . $cycle->getCode(),
                    "packs"  => $packs,
                    "icon"   => $cycle->getCode(),
                ];
            }
        }

        return $cycles;
    }

    public function get_search_rows(array $conditions, string $sortorder, string $locale)
    {
        $i = 0;

        // Construction of the sql request
        $init = $this->entityManager->createQueryBuilder();
        $qb = $init->select('c', 'p', 'y', 't', 'f', 's')
           ->from(Card::class, 'c')
           ->leftJoin('c.pack', 'p')
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
                    $or = [];
                    foreach ($condition as $arg) {
                        $code = preg_match('/^\d\d\d\d\d$/u', $arg);
                        $acronym = preg_match('/^[A-Z]{2,}$/', $arg);
                        if ($code) {
                            $or[] = "(c.code = ?$i)";
                            $parameters[$i++] = $arg;
                        } elseif ($acronym) {
                            $or[] = "(BINARY(c.title) like ?$i)";
                            $parameters[$i++] = "%$arg%";
                            $like = implode('% ', str_split($arg));
                            $or[] = "(REPLACE(c.title, '-', ' ') like ?$i)";
                            $parameters[$i++] = "$like%";
                        } else {
                            if ($arg == 'Franklin') {
                                $arg = 'Crick';
                            } // easter egg
                            $or[] = "(c.title like ?$i)";
                            $parameters[$i++] = "%$arg%";
                        }
                    }
                    $clauses[] = implode(" or ", $or);
                    break;
                case 'x': // text
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.text like ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.text not like ?$i)";
                                break;
                        }
                        $parameters[$i++] = "%$arg%";
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'a': // flavor
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.flavor like ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.flavor not like ?$i)";
                                break;
                        }
                        $parameters[$i++] = "%$arg%";
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'e': // extension (pack)
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(p.code = ?$i)";
                                break;
                            case '!':
                                $or[] = "(p.code != ?$i)";
                                break;
                            case '<':
                                if (!isset($qb2)) {
                                    $qb2 = $this->entityManager->createQueryBuilder()->select('p2')->from(Pack::class, 'p2');
                                    $or[] = $qb->expr()->lt('p.dateRelease', '(' . $qb2->select('p2.dateRelease')->where("p2.code = ?$i")->getDQL() . ')');
                                }
                                break;
                            case '>':
                                if (!isset($qb3)) {
                                    $qb3 = $this->entityManager->createQueryBuilder()->select('p3')->from(Pack::class, 'p3');
                                    $or[] = $qb->expr()->gt('p.dateRelease', '(' . $qb3->select('p3.dateRelease')->where("p3.code = ?$i")->getDQL() . ')');
                                }
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'c': // cycle (cycle)
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(y.position = ?$i)";
                                break;
                            case '!':
                                $or[] = "(y.position != ?$i)";
                                break;
                            case '<':
                                $or[] = "(y.position < ?$i)";
                                break;
                            case '>':
                                $or[] = "(y.position > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 't': // type
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(t.code = ?$i)";
                                break;
                            case '!':
                                $or[] = "(t.code != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'f': // faction
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(f.code = ?$i)";
                                break;
                            case '!':
                                $or[] = "(f.code != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 's': // subtype (keywords)
                    $or = [];
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
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(SUBSTRING(s.code,1,1) = SUBSTRING(?$i,1,1))";
                                break;
                            case '!':
                                $or[] = "(SUBSTRING(s.code,1,1) != SUBSTRING(?$i,1,1))";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'i': // illustrator
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.illustrator = ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.illustrator != ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'o': // cost
                    $or = [];
                    foreach ($condition as $arg) {
                        if (($arg === 'x') or ($arg === 'X')) {
                            switch ($operator) {
                                case ':':
                                    $or[] = "(c.cost is null and (t.code not in ('agenda', 'identity')))";
                                    break;
                                case '!':
                                    $or[] = "(c.cost is not null)";
                                    break;
                            }
                        } else {
                            switch ($operator) {
                                case ':':
                                    $or[] = "(c.cost = ?$i)";
                                    break;
                                case '!':
                                    $or[] = "(c.cost != ?$i)";
                                    break;
                                case '<':
                                    $or[] = "(c.cost < ?$i)";
                                    break;
                                case '>':
                                    $or[] = "(c.cost > ?$i)";
                                    break;
                            }
                            $parameters[$i++] = $arg;
                        }
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'g': // advancementcost
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.advancementCost = ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.advancementCost != ?$i)";
                                break;
                            case '<':
                                $or[] = "(c.advancementCost < ?$i)";
                                break;
                            case '>':
                                $or[] = "(c.advancementCost > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'm': // memoryunits
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.memoryCost = ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.memoryCost != ?$i)";
                                break;
                            case '<':
                                $or[] = "(c.memoryCost < ?$i)";
                                break;
                            case '>':
                                $or[] = "(c.memoryCost > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'n': // influence or influenceLimit
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.factionCost = ?$i or c.influenceLimit =?$i)";
                                break;
                            case '!':
                                $or[] = "(c.factionCost != ?$i or c.influenceLimit != ?$i)";
                                break;
                            case '<':
                                $or[] = "(c.factionCost < ?$i or c.influenceLimit < ?$i)";
                                break;
                            case '>':
                                $or[] = "(c.factionCost > ?$i or c.influenceLimit > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'p': // strength
                    $or = [];
                    foreach ($condition as $arg) {
                        if (($arg === 'x') or ($arg === 'X')) {
                            switch ($operator) {
                                case ':':
                                    $or[] = "(c.strength is null and ((t.code = 'ice') or ((c.keywords = ?$i) or (c.keywords like ?" . ($i + 1) . ") or (c.keywords like ?" . ($i + 2) . ") or (c.keywords like ?" . ($i + 3) . "))))";
                                    $ib = "Icebreaker";
                                    $parameters[$i++] = "$ib";
                                    $parameters[$i++] = "$ib %";
                                    $parameters[$i++] = "% $ib";
                                    $parameters[$i++] = "% $ib %";
                                    break;
                                case '!':
                                    $or[] = "(c.strength is not null)";
                                    break;
                            }
                        } else {
                            switch ($operator) {
                                case ':':
                                    $or[] = "(c.strength = ?$i)";
                                    break;
                                case '!':
                                    $or[] = "(c.strength != ?$i)";
                                    break;
                                case '<':
                                    $or[] = "(c.strength < ?$i)";
                                    break;
                                case '>':
                                    $or[] = "(c.strength > ?$i)";
                                    break;
                            }
                            $parameters[$i++] = $arg;
                        }
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'v': // agendapoints
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.agendaPoints = ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.agendaPoints != ?$i)";
                                break;
                            case '<':
                                $or[] = "(c.agendaPoints < ?$i)";
                                break;
                            case '>':
                                $or[] = "(c.agendaPoints > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'h': // trashcost
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.trashCost = ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.trashCost != ?$i)";
                                break;
                            case '<':
                                $or[] = "(c.trashCost < ?$i)";
                                break;
                            case '>':
                                $or[] = "(c.trashCost > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'y': // quantity
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case ':':
                                $or[] = "(c.quantity = ?$i)";
                                break;
                            case '!':
                                $or[] = "(c.quantity != ?$i)";
                                break;
                            case '<':
                                $or[] = "(c.quantity < ?$i)";
                                break;
                            case '>':
                                $or[] = "(c.quantity > ?$i)";
                                break;
                        }
                        $parameters[$i++] = $arg;
                    }
                    $clauses[] = implode($operator == '!' ? " and " : " or ", $or);
                    break;
                case 'r': // release
                    $or = [];
                    foreach ($condition as $arg) {
                        switch ($operator) {
                            case '<':
                                $or[] = "(p.dateRelease <= ?$i)";
                                break;
                            case '>':
                                $or[] = "(p.dateRelease > ?$i or p.dateRelease IS NULL)";
                                break;
                        }
                        if ($arg == "now") {
                            $parameters[$i++] = new \DateTime();
                        } else {
                            $parameters[$i++] = new \DateTime($arg);
                        }
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
                case 'z': // rotation
                    // Instantiate the service only when its needed.
                    $rotationservice = new RotationService($this->entityManager);
                    $rotation = null;
                    if ($condition[0] == "current" || $condition[0] == "latest") {
                        $rotation = $rotationservice->findCurrentRotation();
                    } else {
                        $rotation = $rotationservice->findRotationByCode($condition[0]);
                    }
                    if ($rotation) {
                        // Add the valid cycles for the requested rotation and add them to the WHERE clause for the query.
                        $cycles = $rotation->normalize()["cycles"];
                        $placeholders = array();
                        foreach($cycles as $cycle) {
                        array_push($placeholders, "?$i");
                            $parameters[$i++] = $cycle;
                        }
                        $clauses[] = "(y.code in (" . implode(", ", $placeholders) . "))";
                    }
                    $i++;
                    break;
            }
        }

        if (count($clauses) === 0) {
            return [];
        }

        foreach ($clauses as $clause) {
            if(!empty($clause)) {
                $qb->andWhere($clause);
            }
        }
        foreach ($parameters as $index => $parameter) {
            $qb->setParameter($index, $parameter);
        }

        switch ($sortorder) {
            case 'name':
                $qb->orderBy('c.title');
                break;
            case 'set':
                $qb->orderBy('p.name')->addOrderBy('c.position');
                break;
            case 'release-date':
                $qb->orderBy('y.position')->addOrderBy('p.position')->addOrderBy('c.position');
                break;
            case 'faction':
                $qb->orderBy('c.side', 'DESC')->addOrderBy('c.faction')->addOrderBy('c.type');
                break;
            case 'type':
                $qb->orderBy('c.side', 'DESC')->addOrderBy('c.type')->addOrderBy('c.faction');
                break;
            case 'cost':
                $qb->orderBy('c.type')->addOrderBy('c.cost')->addOrderBy('c.advancementCost');
                break;
            case 'strength':
                $qb->orderBy('c.type')->addOrderBy('c.strength')->addOrderBy('c.agendaPoints')->addOrderBy('c.trashCost');
                break;
        }
        $query = $qb->getQuery();

        $rows = $query->getResult();

        return $rows;
    }

    /**
     * @param Card $card
     * @return array
     */
    public function getCardInfo(Card $card, string $locale)
    {
        static $cache = [];

        if (isset($cache[$card->getId()]) && isset($cache[$card->getId()][$locale])) {
            return $cache[$card->getId()][$locale];
        }

        $cardinfo = [
            "id"              => $card->getId(),
            "code"            => $card->getCode(),
            "title"           => $card->getTitle(),
            "type_name"       => $card->getType()->getName(),
            "type_code"       => $card->getType()->getCode(),
            "subtype"         => $card->getKeywords(),
            "text"            => $card->getText(),
            "advancementcost" => $card->getAdvancementCost(),
            "agendapoints"    => $card->getAgendaPoints(),
            "baselink"        => $card->getBaseLink(),
            "cost"            => $card->getCost(),
            "faction_name"    => $card->getFaction()->getName(),
            "faction_code"    => $card->getFaction()->getCode(),
            "factioncost"     => $card->getFactionCost(),
            "flavor"          => $card->getFlavor(),
            "illustrator"     => $card->getIllustrator(),
            "influencelimit"  => $card->getInfluenceLimit(),
            "memoryunits"     => $card->getMemoryCost(),
            "minimumdecksize" => $card->getMinimumDeckSize(),
            "position"        => $card->getPosition(),
            "quantity"        => $card->getQuantity(),
            "pack_name"       => $card->getPack()->getName(),
            "pack_code"       => $card->getPack()->getCode(),
            "side_name"       => $card->getSide()->getName(),
            "side_code"       => $card->getSide()->getCode(),
            "strength"        => $card->getStrength(),
            "trash"           => $card->getTrashCost(),
            "uniqueness"      => $card->getUniqueness(),
            "limited"         => $card->getDeckLimit(),
            "cycle_name"      => $card->getPack()->getCycle()->getName(),
            "cycle_code"      => $card->getPack()->getCycle()->getCode(),
            "ancur_link"      => $card->getAncurLink(),
            "imageUrl"        => $card->getImageUrl(),
        ];

        // setting the card cost to X if the cost is null and the card is not of a costless type
        if ($cardinfo['cost'] === null && !in_array($cardinfo['type_code'], ['agenda', 'identity'])) {
            $cardinfo['cost'] = 'X';
        }

        // setting the card strength to X if the strength is null and the card is ICE or Program - Icebreaker
        if ($cardinfo['strength'] === null &&
            ($cardinfo['type_code'] === 'ice' ||
             strstr($cardinfo['subtype'], 'Icebreaker') !== false)) {
            $cardinfo['strength'] = 'X';
        }

        $cardinfo['url'] = $this->router->generate('cards_zoom', ['card_code' => $card->getCode(), '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
        $cardinfo['imageUrl'] = $cardinfo['imageUrl'] ?: $this->packages->getUrl($card->getCode() . ".png", "card_image");

        // replacing <trace>
        $cardinfo['text'] = preg_replace('/<trace>([^<]+) ([X\d]+)<\/trace>/', '<strong>\1 [\2]</strong>–', $cardinfo['text']);

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

    public function syntax(string $query)
    {
        // renvoie une liste de conditions (array)
        // chaque condition est un tableau à n>1 éléments
        // le premier est le type de condition (0 ou 1 caractère)
        // les suivants sont les arguments, en OR

        $query = preg_replace('/\s+/u', ' ', trim($query));

        $list = [];
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
                $match = [];
                if (preg_match('/^(\p{L})([:<>!])(.*)/u', $query, $match)) { // jeton "condition:"
                    $cond = [mb_strtolower($match[1]), $match[2]];
                    $query = $match[3];
                } else {
                    $cond = ["", ":"];
                }
                $etat = 2;
            } else {
                if (preg_match('/^"([^"]*)"(.*)/u', $query, $match) // jeton "texte libre entre guillements"
                    || preg_match('/^([\p{L}\p{N}\-\&\.\!\'\;]+)(.*)/u', $query, $match) // jeton "texte autorisé sans guillements"
                ) {
                    if (($etat == 2 && isset($cond) && count($cond) == 2) || $etat == 3) {
                        $cond[] = $match[1];
                        $query = $match[2];
                        $etat = 2;
                    } else {
                        // erreur
                        $query = $match[2];
                        $etat = 4;
                    }
                } elseif (preg_match('/^\|(.*)/u', $query, $match)) { // jeton "|"
                    if (($cond[1] == ':' || $cond[1] == '!') && (($etat == 2 && isset($cond) && count($cond) > 2) || $etat == 3)) {
                        $query = $match[1];
                        $etat = 3;
                    } else {
                        // erreur
                        $query = $match[1];
                        $etat = 4;
                    }
                } elseif (preg_match('/^ (.*)/u', $query, $match)) { // jeton " "
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

    public function validateConditions(array &$conditions)
    {
        // Remove invalid conditions
        $canDoNumeric = ['c', 'e', 'h', 'm', 'n', 'o', 'p', 'r', 'y'];
        $numeric = ['<', '>'];
        foreach ($conditions as $i => $l) {
            if (in_array($l[1], $numeric) && !in_array($l[0], $canDoNumeric)) {
                unset($conditions[$i]);
            }
            if ($l[0] == 'f') {
                $factions = [];
                for ($j = 1; $j < count($l); ++$j) {
                    if (strlen($l[$j]) === 1) {
                        // replace faction letter with full name
                        $keys = array_keys(self::$faction_letters, $l[$j]);
                        if (count($keys)) {
                            array_push($factions, $keys[0]);
                        }
                    } else {
                        array_push($factions, $l[$j]);
                    }
                }
                array_unshift($factions, 'f', $l[1]);
                $conditions[$i] = $factions;
            }
        }
    }

    public function buildQueryFromConditions(array $conditions)
    {
        // reconstruction de la bonne chaine de recherche pour affichage
        return implode(" ", array_map(
            function ($l) {
                return ($l[0] ? $l[0] . $l[1] : "")
                    . implode("|", array_map(
                        function ($s) {
                            return preg_match("/^[\p{L}\p{N}\-\&\.\!\'\;]+$/u", $s) ? $s : "\"$s\"";
                        },
                        array_slice($l, 2)
                    ));
            },
            $conditions
        ));
    }

    public function get_mwl_info(array $cards)
    {
        $response = [];
        $mwls = $this->entityManager->getRepository(Mwl::class)->findBy([], ["dateStart" => "DESC"]);

        foreach ($cards as $card) {
            $card_code = $card->getCode();
            foreach ($mwls as $mwl) {
                $mwl_cards = $mwl->getCards();
                if (isset($mwl_cards[$card_code])) {
                    $card_mwl = $mwl_cards[$card_code];
                    $is_restricted = $card_mwl['is_restricted'] ?? 0;
                    $deck_limit = $card_mwl['deck_limit'] ?? null;
                    // Ceux-ci signifient la même chose
                    $universal_faction_cost = $card_mwl['universal_faction_cost'] ?? $card_mwl['global_penalty'] ?? 0;
                    $response[] = [
                        'mwl_name'               => $mwl->getName(),
                        'active'                 => $mwl->getActive(),
                        'is_restricted'          => $is_restricted,
                        'deck_limit'             => $deck_limit,
                        'universal_faction_cost' => $universal_faction_cost,
                    ];
                }
            }
        }

        return $response;
    }

    public function get_reviews(array $cards)
    {
        $reviews = $this->entityManager->getRepository(Review::class)->findBy(['card' => $cards], ['nbvotes' => 'DESC']);

        $response = [];
        $packs = $this->packRepository->findBy([], ["dateRelease" => "ASC"]);
        foreach ($reviews as $review) {
            /** @var Review $review */
            $user = $review->getUser();

            $response[] = [
                'id'                => $review->getId(),
                'text'              => $review->getText(),
                'author_id'         => $user->getId(),
                'author_name'       => $user->getUsername(),
                'author_reputation' => $user->getReputation(),
                'author_donation'   => $user->getDonation(),
                'author_color'      => $user->getFaction(),
                'date_creation'     => $review->getDateCreation(),
                'nbvotes'           => $review->getNbvotes(),
                'comments'          => $review->getComments(),
                'latestpack'        => $this->last_pack_for_review($packs, $review),
            ];
        }

        return $response;
    }

    public function last_pack_for_review(array $packs, Review $review)
    {
        /** @var Pack $pack */
        foreach (array_reverse($packs) as $pack) {
            if ($pack->getDateRelease() instanceof \DateTime
                && $pack->getDateRelease() < $review->getDateCreation()) {
                return $pack->getName();
            }
        }

        return 'Unknown';
    }

    public function get_rulings(array $cards)
    {
        $rulings = $this->entityManager->getRepository(Ruling::class)->findBy(['card' => $cards], ['dateCreation' => 'ASC']);

        $response = [];
        foreach ($rulings as $ruling) {
            $response[] = [
                'id'      => $ruling->getId(),
                'text'    => $ruling->getText(),
                'rawtext' => $ruling->getRawtext(),
            ];
        }

        return $response;
    }

    /**
     * Searches a Identity card by its partial title
     * @return \AppBundle\Entity\Card
     */
    public function find_identity(string $partialTitle)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')->from('AppBundle:Card', 'c')->join('AppBundle:Type', 't', 'WITH', 'c.type = t');
        $qb->where($qb->expr()->eq('t.name', ':typeName'));
        $qb->andWhere($qb->expr()->like('c.title', ':title'));
        $query = $qb->getQuery();
        $query->setParameter('typeName', 'Identity');
        $query->setParameter('title', '%' . $partialTitle . '%');
        $card = $query->getSingleResult();

        return $card;
    }

    /**
     *  Searches for other versions/releases of all cards
     *  @return array
     */
    public function get_versions()
    {
        $cards = $this->entityManager->getRepository(Card::class)->findAll();

        $versions = [];
        foreach ($cards as $card) {
            $versions[$card->getTitle()][] = $card;
        }

        return $versions;
    }

    public function select_only_latest_cards(array $matchingCards)
    {
        $latestCardsByTitle = [];
        foreach ($matchingCards as $card) {
            $latestCard = null;
            $title = $card->getTitle();

            if (isset($latestCardsByTitle[$title])) {
                $latestCard = $latestCardsByTitle[$title];
            }
            if (!$latestCard || $card->getCode() > $latestCard->getCode()) {
                $latestCardsByTitle[$title] = $card;
            }
        }

        return array_values(array_filter($matchingCards, function ($card) use ($latestCardsByTitle) {
            return $card->getCode() == $latestCardsByTitle[$card->getTitle()]->getCode();
        }));
    }

    public function get_versions_by_code(array $cards_code)
    {
        $cards = $this->entityManager->getRepository(Card::class)->findBy(['code' => $cards_code]);
        $titles = [];
        foreach ($cards as $card) {
            $titles[] = $card->getTitle();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb = $qb->select('c')
                 ->from(Card::class, 'c')
                 ->where('c.title in (:titles)')
                 ->setParameter('titles', $titles);
        $query = $qb->getQuery();
        $rows = $query->getResult();

        return $rows;
    }
}

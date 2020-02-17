<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\NormalizableInterface;
use AppBundle\Behavior\Entity\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Card
 */
class Card implements NormalizableInterface, TimestampableInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateUpdate;
    
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $keywords;

    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $advancementCost;

    /**
     * @var integer
     */
    private $agendaPoints;

    /**
     * @var integer
     */
    private $baseLink;

    /**
     * @var integer|null
     */
    private $cost;

    /**
     * @var integer|null
     */
    private $factionCost;

    /**
     * @var string|null
     */
    private $flavor;

    /**
     * @var string
     */
    private $illustrator;

    /**
     * @var integer|null
     */
    private $influenceLimit;

    /**
     * @var integer
     */
    private $memoryCost;

    /**
     * @var integer
     */
    private $minimumDeckSize;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var integer
     */
    private $strength;

    /**
     * @var integer|null
     */
    private $trashCost;

    /**
     * @var boolean
     */
    private $uniqueness;

    /**
     * @var integer
     */
    private $deckLimit;

    /**
     * @var Collection
     */
    private $decklists;

    /**
     * @var Pack
     */
    private $pack;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var Faction
     */
    private $faction;

    /**
     * @var Side
     */
    private $side;

    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @var Collection
     */
    private $reviews;

    /**
     * @var Collection
     */
    private $rulings;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var int|null
     */
    private $globalPenalty;

    /**
     * @var int|null
     */
    private $universalFactionCost;

    /**
     * @var bool
     */
    private $isRestricted;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->decklists = new ArrayCollection();
        $this->dateUpdate = new \DateTime();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code . ' ' . $this->title;
    }

    /**
     * @return array
     */
    public function normalize()
    {
        if (empty($this->code)) {
            return [];
        }

        $normalized = [];

        $mandatoryFields = [
                'code',
                'title',
                'position',
                'uniqueness',
                'deck_limit',
                'quantity',
        ];
        if (substr($this->faction->getCode(), 0, 7) === 'neutral' && $this->type->getCode() !== 'identity') {
            $mandatoryFields[] = 'faction_cost';
        }

        $optionalFields = [
                'illustrator',
                'flavor',
                'keywords',
                'text',
                'cost',
                'faction_cost',
                'trash_cost',
                'image_url'
        ];

        $externalFields = [
                'faction',
                'pack',
                'side',
                'type'
        ];

        switch ($this->type->getCode()) {
            case 'identity':
                $mandatoryFields[] = 'influence_limit';
                $mandatoryFields[] = 'minimum_deck_size';
                if ($this->side->getCode() === 'runner') {
                    $mandatoryFields[] = 'base_link';
                }
                break;
            case 'agenda':
                $mandatoryFields[] = 'advancement_cost';
                $mandatoryFields[] = 'agenda_points';
                break;
            case 'asset':
            case 'upgrade':
                $mandatoryFields[] = 'cost';
                $mandatoryFields[] = 'faction_cost';
                $mandatoryFields[] = 'trash_cost';
                break;
            case 'ice':
                $mandatoryFields[] = 'cost';
                $mandatoryFields[] = 'faction_cost';
                $mandatoryFields[] = 'strength';
                break;
            case 'operation':
            case 'event':
            case 'hardware':
            case 'resource':
                $mandatoryFields[] = 'cost';
                $mandatoryFields[] = 'faction_cost';
                break;
            case 'program':
                $mandatoryFields[] = 'cost';
                $mandatoryFields[] = 'faction_cost';
                $mandatoryFields[] = 'memory_cost';
                if (strstr($this->keywords, 'Icebreaker') !== false) {
                    $mandatoryFields[] = 'strength';
                }
                break;
        }

        foreach ($optionalFields as $optionalField) {
            $getter = $this->snakeToCamel('get_' . $optionalField);
            $normalized[$optionalField] = $this->$getter();

            if (!isset($normalized[$optionalField]) || $normalized[$optionalField] === '') {
                unset($normalized[$optionalField]);
            }
        }

        foreach ($mandatoryFields as $mandatoryField) {
            $getter = $this->snakeToCamel('get_' . $mandatoryField);
            $normalized[$mandatoryField] = $this->$getter();
        }

        foreach ($externalFields as $externalField) {
            $getter = $this->snakeToCamel('get_' . $externalField);
            $normalized[$externalField.'_code'] = $this->$getter()->getCode();
        }

        ksort($normalized);
        return $normalized;
    }

    /**
     * @param string $snake
     * @return string
     */
    private function snakeToCamel(string $snake)
    {
        $parts = explode('_', $snake);
        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return $this
     */
    public function setDateUpdate(\DateTime $dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string|null $keywords
     * @return $this
     */
    public function setKeywords(string $keywords = null)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdvancementCost()
    {
        return $this->advancementCost;
    }

    /**
     * @param int $advancementCost
     * @return $this
     */
    public function setAdvancementCost(int $advancementCost)
    {
        $this->advancementCost = $advancementCost;

        return $this;
    }

    /**
     * @return int
     */
    public function getAgendaPoints()
    {
        return $this->agendaPoints;
    }

    /**
     * @param int $agendaPoints
     * @return $this
     */
    public function setAgendaPoints(int $agendaPoints)
    {
        $this->agendaPoints = $agendaPoints;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseLink()
    {
        return $this->baseLink;
    }

    /**
     * @param int $baseLink
     * @return $this
     */
    public function setBaseLink(int $baseLink)
    {
        $this->baseLink = $baseLink;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param int|null $cost
     * @return $this
     */
    public function setCost(int $cost = null)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFactionCost()
    {
        return $this->factionCost === null ? 0 : $this->factionCost;
    }

    /**
     * @param int|null $factionCost
     * @return $this
     */
    public function setFactionCost(int $factionCost = null)
    {
        $this->factionCost = $factionCost;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param string|null $flavor
     * @return $this
     */
    public function setFlavor(string $flavor = null)
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * @return string
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * @param string $illustrator
     * @return $this
     */
    public function setIllustrator(string $illustrator)
    {
        $this->illustrator = $illustrator;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getInfluenceLimit()
    {
        return $this->influenceLimit;
    }

    /**
     * @param int|null $influenceLimit
     * @return $this
     */
    public function setInfluenceLimit(int $influenceLimit = null)
    {
        $this->influenceLimit = $influenceLimit;

        return $this;
    }

    /**
     * @return int
     */
    public function getMemoryCost()
    {
        return $this->memoryCost;
    }

    /**
     * @param int $memoryCost
     * @return $this
     */
    public function setMemoryCost(int $memoryCost)
    {
        $this->memoryCost = $memoryCost;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinimumDeckSize()
    {
        return $this->minimumDeckSize;
    }

    /**
     * @param int $minimumDeckSize
     * @return $this
     */
    public function setMinimumDeckSize(int $minimumDeckSize)
    {
        $this->minimumDeckSize = $minimumDeckSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * @param int $strength
     * @return $this
     */
    public function setStrength(int $strength)
    {
        $this->strength = $strength;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTrashCost()
    {
        return $this->trashCost;
    }

    /**
     * @param int|null $trashCost
     * @return $this
     */
    public function setTrashCost(int $trashCost = null)
    {
        $this->trashCost = $trashCost;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUniqueness()
    {
        return $this->uniqueness;
    }

    /**
     * @param bool $uniqueness
     * @return $this
     */
    public function setUniqueness(bool $uniqueness)
    {
        $this->uniqueness = $uniqueness;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * @param int $deckLimit
     * @return $this
     */
    public function setDeckLimit(int $deckLimit)
    {
        $this->deckLimit = $deckLimit;

        return $this;
    }

    /**
     * @param Decklist $decklists
     * @return $this
     */
    public function addDecklist(Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * @param Decklist $decklists
     */
    public function removeDecklist(Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * @return Pack
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * @param Pack $pack
     * @return $this
     */
    public function setPack(Pack $pack)
    {
        $this->pack = $pack;

        return $this;
    }

    /**
     * @param Review $reviews
     * @return $this
     */
    public function addReview(Review $reviews)
    {
        $this->reviews[] = $reviews;

        return $this;
    }

    /**
     * @param Review $reviews
     */
    public function removeReview(Review $reviews)
    {
        $this->reviews->removeElement($reviews);
    }

    /**
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param Ruling $rulings
     * @return $this
     */
    public function addRuling(Ruling $rulings)
    {
        $this->rulings[] = $rulings;

        return $this;
    }

    /**
     * @param Ruling $rulings
     */
    public function removeRuling(Ruling $rulings)
    {
        $this->rulings->removeElement($rulings);
    }

    /**
     * @return Collection
     */
    public function getRulings()
    {
        return $this->rulings;
    }

    /**
     * @return string
     */
    public function getAncurLink()
    {
        $title = $this->title;
        if ($this->getType()->getName() == "Identity") {
            if ($this->getSide()->getName() == "Runner") {
                $title = preg_replace('/: .*/', '', $title);
            } else {
                if (strstr($title, $this->getFaction()->getName()) === 0) {
                    $title = preg_replace('/.*: /', '', $title);
                } else {
                    $title = preg_replace('/: .*/', '', $title);
                }
            }
        }
        $title_url = preg_replace('/ /', '_', $title);
        return "http://ancur.wikia.com/wiki/".urlencode($title_url);
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     * @return $this
     */
    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * @param Side $side
     * @return $this
     */
    public function setSide(Side $side)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @param Faction $faction
     * @return $this
     */
    public function setFaction(Faction $faction)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityShortTitle()
    {
        $parts = explode(': ', $this->title);
        if (count($parts) > 1 && $parts[0] === $this->faction->getName()) {
            return $parts[1];
        }
        return $parts[0];
    }

    /**
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param \DateTime $dateCreation
     * @return $this
     */
    public function setDateCreation(\DateTime $dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGlobalPenalty()
    {
        return $this->globalPenalty;
    }

    /**
     * @param int|null $globalPenalty
     * @return $this
     */
    public function setGlobalPenalty(int $globalPenalty = null)
    {
        $this->globalPenalty = $globalPenalty;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUniversalFactionCost()
    {
        return $this->universalFactionCost;
    }

    /**
     * @param int|null $universalFactionCost
     * @return $this
     */
    public function setUniversalFactionCost(int $universalFactionCost = null)
    {
        $this->universalFactionCost = $universalFactionCost;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRestricted()
    {
        return $this->isRestricted;
    }

    /**
     * @param bool $isRestricted
     * @return $this
     */
    public function setIsRestricted(bool $isRestricted)
    {
        $this->isRestricted = $isRestricted;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string|null $imageUrl
     * @return $this
     */
    public function setImageUrl(string $imageUrl = null)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}

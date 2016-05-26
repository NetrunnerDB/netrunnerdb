<?php

namespace Netrunnerdb\CardsBundle\Entity;

/**
 * Card
 */
class Card implements \Gedmo\Translatable\Translatable, \Serializable
{
	public function toString() {
		return $this->code . ' ' . $this->title;
	}

	public function serialize() {
		$serialized = [
				'code' => $this->code,
				'pack_code' => $this->pack->getCode(),
				'faction_code' => $this->faction->getCode(),
				'type_code' => $this->type->getCode(),
				'side_code' => $this->side->getCode(),
				'title' => $this->title,
				'position' => $this->position,
				'text' => $this->text,
				'keywords' => $this->keywords,
				'flavor' => $this->flavor,
				'illustrator' => $this->illustrator,
				'uniqueness' => $this->uniqueness,
				'deck_limit' => $this->deckLimit,
				'quantity' => $this->quantity
		];
		
		switch($this->type->getCode()) {
			case 'identity':
				$serialized['base_link'] = $this->baseLink;
				$serialized['influence_limit'] = $this->influenceLimit;
				$serialized['minimum_deck_size'] = $this->minimumDeckSize;
				break;
			case 'agenda':
				$serialized['advancement_cost'] = $this->advancementCost;
				$serialized['agenda_points'] = $this->agendaPoints;
				break;
			case 'asset':
			case 'upgrade':
			case 'operation':
				$serialized['cost'] = $this->cost;
				$serialized['faction_cost'] = $this->factionCost;
				$serialized['trash_cost'] = $this->trashCost;
				break;
			case 'ice':
				$serialized['cost'] = $this->cost;
				$serialized['faction_cost'] = $this->factionCost;
				$serialized['strength'] = $this->strength;
				$serialized['trash_cost'] = $this->trashCost;
				break;
			case 'event':
			case 'hardware':
			case 'resource':
				$serialized['cost'] = $this->cost;
				$serialized['faction_cost'] = $this->factionCost;
				break;
			case 'program':
				$serialized['cost'] = $this->cost;
				$serialized['faction_cost'] = $this->factionCost;
				$serialized['memory_cost'] = $this->memoryCost;
				if(strstr($this->keywords, 'Icebreaker') !== FALSE) {
					$serialized['strength'] = $this->strength;
				}
				break;
		}
		
		return $serialized;
	}
	
	public function unserialize($serialized) {
		throw new \Exception("unserialize() method unsupported");
	}
	
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
     * @var string
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
     * @var integer
     */
    private $cost;

    /**
     * @var integer
     */
    private $factionCost;

    /**
     * @var string
     */
    private $flavor;

    /**
     * @var string
     */
    private $illustrator;

    /**
     * @var integer
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
     * @var integer
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

    private $locale = 'en';
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decklists;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Pack
     */
    private $pack;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Type
     */
    private $type;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Faction
     */
    private $faction;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Side
     */
    private $side;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->decklists = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dateUpdate = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     * @return Card
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Card
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Card
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return Card
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        
        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Card
     */
    public function setText($text)
    {
        $this->text = $text;
        
        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set advancementCost
     *
     * @param integer $advancementCost
     * @return Card
     */
    public function setAdvancementCost($advancementCost)
    {
        $this->advancementCost = $advancementCost;

        return $this;
    }

    /**
     * Get advancementCost
     *
     * @return integer
     */
    public function getAdvancementCost()
    {
        return $this->advancementCost;
    }

    /**
     * Set agendaPoints
     *
     * @param integer $agendaPoints
     * @return Card
     */
    public function setAgendaPoints($agendaPoints)
    {
        $this->agendaPoints = $agendaPoints;

        return $this;
    }

    /**
     * Get agendaPoints
     *
     * @return integer
     */
    public function getAgendaPoints()
    {
        return $this->agendaPoints;
    }

    /**
     * Set baseLink
     *
     * @param integer $baseLink
     * @return Card
     */
    public function setBaseLink($baseLink)
    {
        $this->baseLink = $baseLink;

        return $this;
    }

    /**
     * Get baseLink
     *
     * @return integer
     */
    public function getBaseLink()
    {
        return $this->baseLink;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     * @return Card
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set factionCost
     *
     * @param integer $factionCost
     * @return Card
     */
    public function setFactionCost($factionCost)
    {
        $this->factionCost = $factionCost;

        return $this;
    }

    /**
     * Get factionCost
     *
     * @return integer
     */
    public function getFactionCost()
    {
        return $this->factionCost;
    }

    /**
     * Set flavor
     *
     * @param string $flavor
     * @return Card
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;
        
        return $this;
    }

    /**
     * Get flavor
     *
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Set illustrator
     *
     * @param string $illustrator
     * @return Card
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;

        return $this;
    }

    /**
     * Get illustrator
     *
     * @return string
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * Set influenceLimit
     *
     * @param integer $influenceLimit
     * @return Card
     */
    public function setInfluenceLimit($influenceLimit)
    {
        $this->influenceLimit = $influenceLimit;

        return $this;
    }

    /**
     * Get influenceLimit
     *
     * @return integer
     */
    public function getInfluenceLimit()
    {
        return $this->influenceLimit;
    }

    /**
     * Set memoryCost
     *
     * @param integer $memoryCost
     * @return Card
     */
    public function setMemoryCost($memoryCost)
    {
        $this->memoryCost = $memoryCost;

        return $this;
    }

    /**
     * Get memoryCost
     *
     * @return integer
     */
    public function getMemoryCost()
    {
        return $this->memoryCost;
    }

    /**
     * Set minimumDeckSize
     *
     * @param integer $minimumDeckSize
     * @return Card
     */
    public function setMinimumDeckSize($minimumDeckSize)
    {
        $this->minimumDeckSize = $minimumDeckSize;

        return $this;
    }

    /**
     * Get minimumDeckSize
     *
     * @return integer
     */
    public function getMinimumDeckSize()
    {
        return $this->minimumDeckSize;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Card
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Card
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set strength
     *
     * @param integer $strength
     * @return Card
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;

        return $this;
    }

    /**
     * Get strength
     *
     * @return integer
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * Set trashCost
     *
     * @param integer $trashCost
     * @return Card
     */
    public function setTrashCost($trashCost)
    {
        $this->trashCost = $trashCost;

        return $this;
    }

    /**
     * Get trashCost
     *
     * @return integer
     */
    public function getTrashCost()
    {
        return $this->trashCost;
    }

    /**
     * Set uniqueness
     *
     * @param boolean $uniqueness
     * @return Card
     */
    public function setUniqueness($uniqueness)
    {
        $this->uniqueness = $uniqueness;

        return $this;
    }

    /**
     * Get uniqueness
     *
     * @return boolean
     */
    public function getUniqueness()
    {
        return $this->uniqueness;
    }

    /**
     * Set deckLimit
     *
     * @param integer $deckLimit
     * @return Card
     */
    public function setDeckLimit($deckLimit)
    {
        $this->deckLimit = $deckLimit;

        return $this;
    }

    /**
     * Get deckLimit
     *
     * @return integer
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * Add decklists
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklists
     * @return Card
     */
    public function addDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $decklists
     */
    public function removeDecklist(\Netrunnerdb\BuilderBundle\Entity\Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
    }

    /**
     * Get decklists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * Set pack
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Pack $pack
     * @return Card
     */
    public function setPack(\Netrunnerdb\CardsBundle\Entity\Pack $pack = null)
    {
        $this->pack = $pack;

        return $this;
    }

    /**
     * Get pack
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Pack
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * Set type
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Type $type
     * @return Card
     */
    public function setType(\Netrunnerdb\CardsBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set faction
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Faction $faction
     * @return Card
     */
    public function setFaction(\Netrunnerdb\CardsBundle\Entity\Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * Set side
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Side $side
     * @return Card
     */
    public function setSide(\Netrunnerdb\CardsBundle\Entity\Side $side = null)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get side
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Side
     */
    public function getSide()
    {
        return $this->side;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reviews;


    /**
     * Add reviews
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Review $reviews
     * @return Card
     */
    public function addReview(\Netrunnerdb\BuilderBundle\Entity\Review $reviews)
    {
        $this->reviews[] = $reviews;

        return $this;
    }

    /**
     * Remove reviews
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Review $reviews
     */
    public function removeReview(\Netrunnerdb\BuilderBundle\Entity\Review $reviews)
    {
        $this->reviews->removeElement($reviews);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }
    
    public function getAncurLink()
    {
        $title = $this->title;
        if($this->getType()->getName() == "Identity") {
            if($this->getSide()->getName() == "Runner") {
                $title = preg_replace('/: .*/', '', $title);
            } else {
                if(strstr($title, $this->getFaction()->getName()) === 0) {
                    $title = preg_replace('/.*: /', '', $title);
                } else {
                    $title = preg_replace('/: .*/', '', $title);
                }
            }
        }
        $title_url = preg_replace('/ /', '_', $title);
        return "http://ancur.wikia.com/wiki/".urlencode($title_url);
    }
    
    public function getIdentityShortTitle()
    {
        $parts = explode(': ', $this->title);
        if(count($parts) > 1 && $parts[0] === $this->faction->getName()) {
            return $parts[1];
        }
        return $parts[0];
    }
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    /**
     * @var \DateTime
     */
    private $dateCreation;


    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Card
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
}

<?php

namespace AppBundle\Entity;

/**
 * Card
 */
class Card implements \Gedmo\Translatable\Translatable, \Serializable
{
	public function toString() {
		return $this->code . ' ' . $this->title;
	}

	private function snakeToCamel($snake) {
		$parts = explode('_', $snake);
		return implode('', array_map('ucfirst', $parts));
	}
	
	public function serialize() {
		$serialized = [];
		if(empty($this->code)) return $serialized;
		
		$mandatoryFields = [
				'code',
				'title',
				'position',
				'uniqueness',
				'deck_limit',
				'quantity',
		];
		if(substr($this->faction->getCode(), 0, 7) === 'neutral' && $this->type->getCode() !== 'identity') {
			$mandatoryFields[] = 'faction_cost';
		}

		$optionalFields = [
				'illustrator',
				'flavor',
				'keywords',
				'text',
				'cost',
				'faction_cost',
				'trash_cost'
		];

		$externalFields = [
				'faction',
				'pack',
				'side',
				'type'
		];
		
		switch($this->type->getCode()) {
			case 'identity':
				$mandatoryFields[] = 'influence_limit';
				$mandatoryFields[] = 'minimum_deck_size';
				if($this->side->getCode() === 'runner') {
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
				if(strstr($this->keywords, 'Icebreaker') !== FALSE) {
					$mandatoryFields[] = 'strength';
				}
				break;
		}

		foreach($optionalFields as $optionalField) {
			$getter = 'get' . $this->snakeToCamel($optionalField);
			$serialized[$optionalField] = $this->$getter();
			if(!isset($serialized[$optionalField]) || $serialized[$optionalField] === '') unset($serialized[$optionalField]);
		}
		
		foreach($mandatoryFields as $mandatoryField) {
			$getter = 'get' . $this->snakeToCamel($mandatoryField);
			$serialized[$mandatoryField] = $this->$getter();
		}

		foreach($externalFields as $externalField) {
			$getter = 'get' . $this->snakeToCamel($externalField);
			$serialized[$externalField.'_code'] = $this->$getter()->getCode();
		}
		
		ksort($serialized);
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
     * @var \AppBundle\Entity\Pack
     */
    private $pack;

    /**
     * @var \AppBundle\Entity\Type
     */
    private $type;

    /**
     * @var \AppBundle\Entity\Faction
     */
    private $faction;

    /**
     * @var \AppBundle\Entity\Side
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
     * @param \AppBundle\Entity\Decklist $decklists
     * @return Card
     */
    public function addDecklist(\AppBundle\Entity\Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     *
     * @param \AppBundle\Entity\Decklist $decklists
     */
    public function removeDecklist(\AppBundle\Entity\Decklist $decklists)
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
     * @param \AppBundle\Entity\Pack $pack
     * @return Card
     */
    public function setPack(\AppBundle\Entity\Pack $pack = null)
    {
        $this->pack = $pack;

        return $this;
    }

    /**
     * Get pack
     *
     * @return \AppBundle\Entity\Pack
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\Type $type
     * @return Card
     */
    public function setType(\AppBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set faction
     *
     * @param \AppBundle\Entity\Faction $faction
     * @return Card
     */
    public function setFaction(\AppBundle\Entity\Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
     * @return \AppBundle\Entity\Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * Set side
     *
     * @param \AppBundle\Entity\Side $side
     * @return Card
     */
    public function setSide(\AppBundle\Entity\Side $side = null)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get side
     *
     * @return \AppBundle\Entity\Side
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
     * @param \AppBundle\Entity\Review $reviews
     * @return Card
     */
    public function addReview(\AppBundle\Entity\Review $reviews)
    {
        $this->reviews[] = $reviews;

        return $this;
    }

    /**
     * Remove reviews
     *
     * @param \AppBundle\Entity\Review $reviews
     */
    public function removeReview(\AppBundle\Entity\Review $reviews)
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
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $rulings;

    /**
     * Add rulings
     *
     * @param \AppBundle\Entity\Ruling $rulings
     * @return Card
     */
    public function addRuling(\AppBundle\Entity\Ruling $rulings)
    {
        $this->rulings[] = $rulings;

        return $this;
    }

    /**
     * Remove rulings
     *
     * @param \AppBundle\Entity\Ruling $rulings
     */
    public function removeRuling(\AppBundle\Entity\Ruling $rulings)
    {
        $this->rulings->removeElement($rulings);
    }

    /**
     * Get rulings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRulings()
    {
        return $this->rulings;
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

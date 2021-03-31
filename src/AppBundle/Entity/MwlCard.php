<?php

namespace AppBundle\Entity;

use AppBundle\Behavior\Entity\NormalizableInterface;

/**
 * MwlCard 
 */
class MwlCard implements NormalizableInterface 
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $mwl_id;

    /**
     * @var integer
     */
    private $card_id;

    /**
     * @var integer|null
     */
	private $global_penalty;

    /**
     * @var integer|null
     */
	private $universal_faction_cost;

    /**
     * @var bool|null
     */
	private $is_restricted;

    /**
     * @var bool|null
     */
     private $is_banned;

    /**
     * @var Mwl 
     */
    private $mwl;

    /**
     * @var Card
     */
    private $card;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getMwlId()
    {
        return $this->mwl_id;
    }

    /**
     * @return integer
     */
    public function getCardId()
    {
        return $this->card_id;
    }

    /**
     * @return integer|null
     */
    public function getGlobalPenalty()
    {
        return $this->global_penalty;
    }

    /**
     * @param integer|null $global_penalty
     * @return MwlCard 
     */
    public function setGlobalPenalty(int $global_penalty = null)
    {
        $this->global_penalty = $global_penalty;

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getUniversalFactionCost()
    {
        return $this->universal_faction_cost;
    }

    /**
     * @param integer|null $universal_faction_cost
     * @return MwlCard
     */
    public function setUniversalFactionCost(int $universal_faction_cost = null)
    {
        $this->universal_faction_cost = $universal_faction_cost;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsRestricted()
    {
        return $this->is_restricted;
    }

    /**
     * @param bool|null $is_restricted
     * @return MwlCard
     */
    public function setIsRestricted(bool $is_restricted = null)
    {
        $this->is_restricted = $is_restricted;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsBanned()
    {
        return $this->is_banned;
    }

    /**
     * @param bool|null $is_banned
     * @return MwlCard 
     */
    public function setIsBanned(bool $is_banned = null)
    {
        $this->is_banned = $is_banned;

        return $this;
    }

    /**
     * @return Mwl 
     */
    public function getMwl()
    {
        return $this->mwl;
    }

    /**
     * @param Mwl $mwl
     * @return $this
     */
    public function setMwl(Mwl $mwl)
    {
        $this->mwl = $mwl;

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card $card
     * @return $this
     */
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return array
     */
    public function normalize()
    {
        return [
            'id'            => $this->id,
            'mwl_id'        => $this->mwl->id,
            'card_id'       => $this->card->id,
            'global_penalty' => $this->global_penalty,
            'universal_faction_cost' => $this->universal_faction_cost,
            'is_restricted' => $this->is_restricted,
            'is_banned' => $this->is_banned,
        ];
    }
}

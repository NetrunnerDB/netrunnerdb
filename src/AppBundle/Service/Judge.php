<?php

namespace AppBundle\Service;

use AppBundle\Behavior\Entity\SlotInterface;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Decklistslot;
use AppBundle\Entity\Legality;
use AppBundle\Entity\Mwl;
use AppBundle\Entity\Card;
use Doctrine\Common\Collections\Collection;

class Judge
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /**
     * @var array
     */
    private $mwlCards;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        $this->mwlCards = [];
    }

    /**
     * @param Card $card
     * @param Mwl  $mwl
     */
    private function getModifiedCard($card, $mwl)
    {
        if (isset($this->mwlCards[$mwl->getId()]) && isset($this->mwlCards[$mwl->getId()][$card->getCode()])) {
            return $this->mwlCards[$mwl->getId()][$card->getCode()];
        }

        $modifiedCard = $card;

        if (array_key_exists($card->getCode(), $mwl->getCards())) {
            $modificationData = $mwl->getCards()[$card->getCode()];
            $modifiedCard = clone($card);
            foreach ($modificationData as $modificationKey => $modificationValue) {
                $setter = 'set'.$this->getTrainCase($modificationKey);
                $modifiedCard->$setter($modificationValue);
            }
        }

        $this->mwlCards[$mwl->getId()][$card->getCode()] = $modifiedCard;

        return $modifiedCard;
    }

    private function getTrainCase($string)
    {
        return implode('', array_map(function ($segment) {
            return ucfirst($segment);
        }, explode('_', $string)));
    }

    /**
     * Decoupe un deckcontent pour son affichage par type
     *
     * @param \AppBundle\Entity\Card $identity
     */
    public function classe($slots, $identity)
    {
        $analyse = $this->analyse($slots);

        $classeur = [];
        /* @var $slot \AppBundle\Entity\Deckslot */
        foreach ($slots as $slot) {
            /* @var $card \AppBundle\Entity\Card */
            $card = $slot->getCard();
            $qty = $slot->getQuantity();
            $elt = ['card' => $card, 'qty' => $qty];
            $type = $card->getType()->getName();
            if ($type == "Identity")
                continue;
            if ($type == "ICE") {
                $keywords = explode(" - ", $card->getKeywords());
                if (in_array("Barrier", $keywords))
                    $type = "Barrier";
                if (in_array("Code Gate", $keywords))
                    $type = "Code Gate";
                if (in_array("Sentry", $keywords))
                    $type = "Sentry";
            }
            if ($type == "Program") {
                $keywords = explode(" - ", $card->getKeywords());
                if (in_array("Icebreaker", $keywords))
                    $type = "Icebreaker";
            }
            $elt['influence'] = $this->getInfluenceCostOfCard($slot, $slots, $identity);
            $elt['faction'] = str_replace(' ', '-', mb_strtolower($card->getFaction()->getName()));

            if (!isset($classeur[$type]))
                $classeur[$type] = ["qty" => 0, "slots" => []];
            $classeur[$type]["slots"][] = $elt;
            $classeur[$type]["qty"] += $qty;
        }
        if (is_string($analyse)) {
            $classeur['problem'] = $this->problem($analyse);
        } else {
            $classeur = array_merge($classeur, $analyse);
        }

        return $classeur;
    }

    public function countCards($slots, $skipIdentity = false)
    {
        return array_reduce($slots, function ($carry, $item) use ($skipIdentity) {
            if ($skipIdentity && $item->getCard()->getType()->getName() === 'Identity') {
                return $carry;
            }

            return $carry + $item->getQuantity();
        }, 0);
    }

    /**
     * @param SlotInterface              $slot
     * @param SlotInterface[]|Collection $slots
     * @param Card                       $identity
     * @return int
     */
    public function getInfluenceCostOfCard($slot, $slots, Card $identity)
    {
        $arraySlots = $slots instanceof Collection ? $slots->toArray() : $slots;
        $card = $slot->getCard();
        $qty = $slot->getQuantity();

        if ($card->getType()->getName() === 'Identity') {
            return 0;
        }
        if ($card->getFaction()->getId() === $identity->getFaction()->getId()) {
            return 0;
        }
        if ($identity->getCode() === '03029' && $card->getType()->getName() === 'Program') {
            return $card->getFactionCost() * ($qty - 1);
        }
        if ($card->getCode() === '10018') {
            // Mumba Temple: 15 or fewer ice => 0 inf
            $targets = array_filter($arraySlots, function ($potentialTarget) {
                /** @var SlotInterface $potentialTarget */
                return $potentialTarget->getCard()->getType()->getCode() === 'ice';
            });
            if ($this->countCards($targets) <= 15) {
                return 0;
            }
        }
        if ($card->getCode() === '10019') {
            // Museum of History: 50 or more cards => 0 inf
            if ($this->countCards($arraySlots, true) >= 50) {
                return 0;
            }
        }
        if ($card->getCode() === '10038') {
            // PAD Factory: 3 PAD Campaign => 0 inf
            $targets = array_filter($arraySlots, function ($potentialTarget) {
                /** @var SlotInterface $potentialTarget */
                $code = $potentialTarget->getCard()->getCode();

                return $code === '01109' || $code === '20128';
            });
            if ($this->countCards($targets) === 3) {
                return 0;
            }
        }
        if ($card->getCode() === '10076') {
            // Mumbad Virtual Tour: 7 or more assets => 0 inf
            $targets = array_filter($arraySlots, function ($potentialTarget) {
                /** @var SlotInterface $potentialTarget */
                return $potentialTarget->getCard()->getType()->getCode() === 'asset';
            });
            if ($this->countCards($targets) >= 7) {
                return 0;
            }
        }
        if ($card->getKeywords() && strpos($card->getKeywords(), 'Alliance') !== false) {
            // 6 or more non-alliance cards of the same faction
            $targets = array_filter($arraySlots, function ($potentialTarget) use ($card) {
                /** @var SlotInterface $potentialTarget */
                return $potentialTarget->getCard()->getFaction()->getId() === $card->getFaction()->getId() && strpos($potentialTarget->getCard()->getKeywords(), 'Alliance') === false;
            });
            if ($this->countCards($targets, true) >= 6) {
                return 0;
            }
        }

        return $card->getFactionCost() * $qty;
    }

    /**
     * @param SlotInterface[] $slots
     * @return array|string
     */
    public function analyse($slots)
    {
        $identity = null;
        $deckSize = 0;
        $influenceSpent = 0;
        $agendaPoints = 0;
        $restricted = false;

        /** @var SlotInterface $slot */
        foreach ($slots as $slot) {
            $card = $slot->getCard();
            $qty = $slot->getQuantity();
            if ($card->getType()->getName() == "Identity") {
                if (isset($identity))
                    return 'identities';
                $identity = $card;
            } else {
                $deckSize += $qty;
            }
        }

        if ($identity === null) {
            return 'identity';
        }

        if ($deckSize < $identity->getMinimumDeckSize()) {
            return 'deckSize';
        }

        $influenceLimit = $identity->getInfluenceLimit();

        /** @var SlotInterface $slot */
        foreach ($slots as $slot) {
            $card = $slot->getCard();
            $qty = $slot->getQuantity();

            if ($card->getType()->getCode() === "identity") {
                continue;
            }

            if ($qty > $card->getDeckLimit() && $identity->getPack()->getCode() != "draft") {
                return 'copies';
            }

            if ($card->getSide() !== $identity->getSide()) {
                return 'side';
            }

            if ($identity->getCode() == "03002" && $card->getFaction()->getCode() === "jinteki") {
                return 'forbidden';
            }

            if ($card->getType()->getCode() == "agenda") {
                if ($card->getFaction()->getCode() !== "neutral"
                    && $card->getFaction() !== $identity->getFaction()
                    && $identity->getFaction()->getCode() !== "neutral"
                ) {
                    return 'agendas';
                }
                $agendaPoints += $card->getAgendaPoints() * $qty;
            }

            $influenceSpent += $this->getInfluenceCostOfCard($slot, $slots, $identity);

            if ($card->getGlobalPenalty() !== null && $influenceLimit !== null) {
                $influenceLimit -= $card->getGlobalPenalty() * $slot->getQuantity();
            }

            if ($card->getUniversalFactionCost() !== null) {
                $influenceSpent += $card->getUniversalFactionCost() * $slot->getQuantity();
            }

            if ($card->isRestricted()) {
                if ($restricted) {
                    return 'restricted';
                }
                $restricted = true;
            }
        }

        if ($influenceLimit !== null && $influenceSpent > $influenceLimit) {
            return 'influence';
        }

        // agenda points rule, except for draft identities because Cube
        if ($identity->getSide()->getCode() == "corp" && $identity->getPack()->getCode() != "draft") {
            $minAgendaPoints = floor($deckSize / 5) * 2 + 2;
            if ($agendaPoints < $minAgendaPoints || $agendaPoints > $minAgendaPoints + 1)
                return 'agendapoints';
        }

        return [
            'deckSize'       => $deckSize,
            'influenceSpent' => $influenceSpent,
            'agendaPoints'   => $agendaPoints,
        ];
    }

    public function getInfuenceLimit(Decklist $decklist)
    {
        foreach ($decklist->getSlots() as $slot) {
            $card = $slot->getCard();
            if ($card->getType()->getName() == "Identity") {
                return $card->getInfluenceLimit();
            }
        }
    }

    public function getSpentInfluence(Decklist $decklist)
    {
        $influenceSpent = 0;

        $identity = $decklist->getIdentity();

        if (!isset($identity)) {
            return null;
        }

        /* @var $slot \AppBundle\Entity\Decklistslot */
        foreach ($decklist->getSlots() as $slot) {
            $influenceCostOfCard = $this->getInfluenceCostOfCard($slot, $decklist->getSlots(), $identity);
            $influenceSpent += $influenceCostOfCard;
        }

        return $influenceSpent;
    }

    public function problem($problem)
    {
        switch ($problem) {
            case 'identity':
                return "The deck lacks an Identity card.";
                break;
            case 'identities':
                return "The deck has more than 1 Identity card;";
                break;
            case 'deckSize':
                return "The deck has less cards than the minimum required by the Identity.";
                break;
            case 'side':
                return "The deck mixes Corp and Runner cards.";
                break;
            case 'forbidden':
                return "The deck includes forbidden cards.";
                break;
            case 'agendas':
                return "The deck uses Agendas from a different faction.";
                break;
            case 'influence':
                return "The deck spends more influence than available on the Identity.";
                break;
            case 'agendapoints':
                return "The deck has a wrong number of Agenda Points.";
                break;
            case 'copies' :
                return "The deck has too many copies of a card.";
                break;
        }
    }

    /**
     * computing whether Legality.decklist is legal under Legality.mwl
     *
     * @param Legality $legality
     * @return bool
     */
    public function computeLegality(Legality $legality)
    {
        /** @var SlotInterface[] $slots */
        $slots = [];

        foreach ($legality->getDecklist()->getSlots() as $slot) {
            $modifiedSlot = new Decklistslot();
            $modifiedSlot->setQuantity($slot->getQuantity());
            $modifiedSlot->setCard($this->getModifiedCard($slot->getCard(), $legality->getMwl()));
            $slots[] = $modifiedSlot;
        }

        $analyse = $this->analyse($slots);

        $legality->setIsLegal(is_array($analyse));

        return $legality->getIsLegal();
    }
}

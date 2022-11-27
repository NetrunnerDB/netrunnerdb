<?php

namespace AppBundle\Service;

use AppBundle\Behavior\Entity\SlotInterface;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Decklistslot;
use AppBundle\Entity\Deckslot;
use AppBundle\Entity\Legality;
use AppBundle\Entity\Mwl;
use AppBundle\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;

class Judge
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var array $mwlCards */
    private $mwlCards;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->mwlCards = [];
    }

    /**
     * @param Card $card
     * @param Mwl $mwl
     */
    private function getModifiedCard(Card $card, Mwl $mwl)
    {
        if (isset($this->mwlCards[$mwl->getId()]) && isset($this->mwlCards[$mwl->getId()][$card->getCode()])) {
            return $this->mwlCards[$mwl->getId()][$card->getCode()];
        }

        $modifiedCard = $card;

        if (array_key_exists($card->getCode(), $mwl->getCards())) {
            $modificationData = $mwl->getCards()[$card->getCode()];
            $modifiedCard = clone($card);
            foreach ($modificationData as $modificationKey => $modificationValue) {
                $setter = 'set' . $this->getTrainCase($modificationKey);
                $modifiedCard->$setter($modificationValue);
            }
        }

        $this->mwlCards[$mwl->getId()][$card->getCode()] = $modifiedCard;

        return $modifiedCard;
    }

    private function getTrainCase(string $string)
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
    public function classe(array $slots, Card $identity)
    {
        $analyse = $this->analyse($slots);

        $classeur = [];
        /** @var Deckslot $slot */
        foreach ($slots as $slot) {
            /** @var Card $card */
            $card = $slot->getCard();
            $qty = $slot->getQuantity();
            $elt = ['card' => $card, 'qty' => $qty];
            $type = $card->getType()->getName();
            if ($type == "Identity") {
                continue;
            }
            if ($type == "Ice") {
                $keywords = explode(" - ", $card->getKeywords());
                if (in_array("Barrier", $keywords)) {
                    $type = "Barrier";
                }
                if (in_array("Code Gate", $keywords)) {
                    $type = "Code Gate";
                }
                if (in_array("Sentry", $keywords)) {
                    $type = "Sentry";
                }
            }
            if ($type == "Program") {
                $keywords = explode(" - ", $card->getKeywords());
                if (in_array("Icebreaker", $keywords)) {
                    $type = "Icebreaker";
                }
            }
            $elt['influence'] = $this->getInfluenceCostOfCard($slot, $slots, $identity);
            $elt['faction'] = str_replace(' ', '-', mb_strtolower($card->getFaction()->getName()));

            if (!isset($classeur[$type])) {
                $classeur[$type] = ["qty" => 0, "slots" => []];
            }
            $classeur[$type]["slots"][] = $elt;
            $classeur[$type]["qty"] += $qty;
        }

        $classeur = array_merge($classeur, $analyse);

        if (isset($analyse['problem'])) {
            $classeur['problem'] = $this->problem($analyse);
        }

        return $classeur;
    }

    public function countCards(array $slots, bool $skipIdentity = false)
    {
        return array_reduce($slots, function ($carry, SlotInterface $item) use ($skipIdentity) {
            if ($skipIdentity && $item->getCard()->getType()->getName() === 'Identity') {
                return $carry;
            }

            return $carry + $item->getQuantity();
        }, 0);
    }

    /**
     * @param SlotInterface $slot
     * @param SlotInterface[] $slots
     * @param Card $identity
     * @return float|int
     */
    public function getInfluenceCostOfCard(SlotInterface $slot, array $slots, Card $identity)
    {
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
            $targets = array_filter($slots, function ($potentialTarget) {
                /** @var SlotInterface $potentialTarget */
                return $potentialTarget->getCard()->getType()->getCode() === 'ice';
            });
            if ($this->countCards($targets) <= 15) {
                return 0;
            }
        }
        if ($card->getCode() === '10019') {
            // Museum of History: 50 or more cards => 0 inf
            if ($this->countCards($slots, true) >= 50) {
                return 0;
            }
        }
        if ($card->getCode() === '10038') {
            // PAD Factory: 3 PAD Campaign => 0 inf
            $targets = array_filter($slots, function ($potentialTarget) {
                /** @var SlotInterface $potentialTarget */
                $code = $potentialTarget->getCard()->getCode();

                return $code === '01109' || $code === '20128' || $code === '25142' || $code === '31080';
            });
            if ($this->countCards($targets) === 3) {
                return 0;
            }
        }
        if ($card->getCode() === '10076') {
            // Mumbad Virtual Tour: 7 or more assets => 0 inf
            $targets = array_filter($slots, function ($potentialTarget) {
                /** @var SlotInterface $potentialTarget */
                return $potentialTarget->getCard()->getType()->getCode() === 'asset';
            });
            if ($this->countCards($targets) >= 7) {
                return 0;
            }
        }
        if ($card->getKeywords() && strpos($card->getKeywords(), 'Alliance') !== false) {
            // 6 or more non-alliance cards of the same faction
            $targets = array_filter($slots, function ($potentialTarget) use ($card) {
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
    public function analyse(array $slots)
    {
        $identity = null;
        $deckSize = 0;
        $influenceSpent = 0;
        $agendaPoints = 0;
        $restricted = false;
        $problem = null;

        /** @var SlotInterface $slot */
        foreach ($slots as $slot) {
            $card = $slot->getCard();
            $qty = $slot->getQuantity();
            if ($card->getType()->getName() == "Identity") {
                if (isset($identity)) {
                    $problem = 'identities';
                }
                $identity = $card;
            } else {
                $deckSize += $qty;
            }
        }

        if ($identity === null) {
            $problem = 'identity';
        }

        if ($deckSize < $identity->getMinimumDeckSize()) {
            $problem = 'deckSize';
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
                $problem = 'copies';
            }

            if (($identity->getCode() == '34093' /* Nova Initiumia */ || $identity->getCode() == '34128' /* Ampere */) && $qty > 1) {
                $problem = 'copies';
            }

            if ($card->getSide() !== $identity->getSide()) {
                $problem = 'side';
            }

            if ($identity->getCode() == "03002" && $card->getFaction()->getCode() === "jinteki") {
                $problem = 'forbidden';
            }

            if ($card->getType()->getCode() === "agenda") {
                if ($card->getFaction()->getCode() !== "neutral-corp"
                    && $card->getFaction() !== $identity->getFaction()
                    && $identity->getFaction()->getCode() !== "neutral-corp"
                ) {
                    $problem = 'agendas';
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
                    $problem = 'restricted';
                }
                $restricted = true;
            }
        }

        if (!($influenceLimit == null || $influenceLimit == 0) && $influenceSpent > $influenceLimit) {
            $problem = 'influence';
        }

        // agenda points rule, except for draft identities because Cube
        if ($identity->getSide()->getCode() == "corp" && $identity->getPack()->getCode() != "draft") {
            $minAgendaPoints = floor($deckSize / 5) * 2 + 2;
            if ($agendaPoints < $minAgendaPoints || $agendaPoints > $minAgendaPoints + 1) {
                $problem = 'agendapoints';
            }
        }

        $result = [
            'deckSize'       => $deckSize,
            'influenceSpent' => $influenceSpent,
            'agendaPoints'   => $agendaPoints,
        ];

        if (isset($problem)) {
            $result['problem'] = $problem;
        }

        return $result;
    }

    public function getInfuenceLimit(Decklist $decklist)
    {
        foreach ($decklist->getSlots() as $slot) {
            $card = $slot->getCard();
            if ($card->getType()->getName() == "Identity") {
                return $card->getInfluenceLimit();
            }
        }

        return 0;
    }

    public function getSpentInfluence(Decklist $decklist)
    {
        $influenceSpent = 0;

        /** @var Decklistslot $slot */
        foreach ($decklist->getSlots() as $slot) {
            $influenceCostOfCard = $this->getInfluenceCostOfCard(
                $slot,
                $decklist->getSlots()->toArray(),
                $decklist->getIdentity()
            );
            $influenceSpent += $influenceCostOfCard;
        }

        return $influenceSpent;
    }

    public function problem($analyse)
    {
        switch ($analyse['problem']) {
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
                return "The deck has the wrong number of Agenda Points.";
                break;
            case 'copies':
                return "The deck has too many copies of a card.";
                break;
        }

        return null;
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

        $legality->setIsLegal(!isset($analyse['problem']));

        return $legality->getIsLegal();
    }
}

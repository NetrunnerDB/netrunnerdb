<?php

namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Description of PersonalizationHelper
 *
 * @author cedric
 */
class PersonalizationHelper
{

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    
    /** @var ActivityHelper $activityHelper */
    private $activityHelper;
    
    /** @var AuthorizationCheckerInterface $authorizationChecker */
    private $authorizationChecker;
    
    public function __construct(EntityManagerInterface $entityManager, ActivityHelper $activityHelper, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->activityHelper = $activityHelper;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     *
     * @param User $user
     * @return array
     */
    public function defaultBlock(User $user)
    {
        return [
            'is_authenticated' => true,
            'id' => $user->getId(),
            'name' => $user->getUsername(),
            'introductions' => $user->getIntroductions(),
            'faction' => $user->getFaction(),
            'autoload_images' => $user->getAutoloadImages(),
            'donation' => $user->getDonation(),
            'unchecked_activity' => $this->activityHelper->countUncheckedItems($this->activityHelper->getItems($user)),
            'is_moderator' => $this->authorizationChecker->isGranted('ROLE_MODERATOR'),
            'roles' => $user->getRoles(),
            'following' => array_map(function ($following) {
                return $following->getId();
            }, $user->getFollowing()->toArray())
        ];
    }

    public function decklistBlock(User $user, Decklist $decklist)
    {
        $dbh = $this->entityManager->getConnection();

        $content = [];

        $content['is_liked'] = (boolean) $dbh->executeQuery("SELECT
                            count(*)
                            from decklist d
                            join vote v on v.decklist_id=d.id
                            where v.user_id=?
                            and d.id=?", array($user->getId(), $decklist->getId()))->fetch(PDO::FETCH_NUM)[0];

        $content['is_favorite'] = (boolean) $dbh->executeQuery("SELECT
                            count(*)
                            from decklist d
                            join favorite f on f.decklist_id=d.id
                            where f.user_id=?
                            and d.id=?", array($user->getId(), $decklist->getId()))->fetch(PDO::FETCH_NUM)[0];

        $content['is_author'] = ($user->getId() == $decklist->getUser()->getId());

        $content['can_delete'] = ($decklist->getNbcomments() == 0) && ($decklist->getNbfavorites() == 0) && ($decklist->getNbvotes() == 0);

        if ($this->authorizationChecker->isGranted('ROLE_MODERATOR')
                or ($content['is_author'] and $decklist->getModerationStatus() === Decklist::MODERATION_TRASHED)) {
            $content['moderation_status'] = $decklist->getModerationStatus();
            $content['moderation_reason'] = $decklist->getModflag() ? $decklist->getModflag()->getReason() : null;
        }
        
        return $content;
    }

    public function cardBlock(User $user, Card $card)
    {
        $content = [];
        
        $reviews = $card->getReviews();
        /* @var $review Review */
        foreach ($reviews as $review) {
            if ($review->getUser()->getId() === $user->getId()) {
                $content['review_id'] = $review->getId();
                $content['review_text'] = $review->getRawtext();
            }
        }
        
        return $content;
    }
}

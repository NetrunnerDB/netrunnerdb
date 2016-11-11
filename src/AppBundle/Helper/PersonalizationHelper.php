<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Card;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PDO;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Description of PersonalizationHelper
 *
 * @author cedric
 */
class PersonalizationHelper
{

    /** @var Registry */
    private $doctrine;
    
    /** @var ActivityHelper */
    private $activityHelper;
    
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;
    
    public function __construct (Registry $doctrine, ActivityHelper $activityHelper, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->doctrine = $doctrine;
        $this->activityHelper = $activityHelper;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * 
     * @param User $user
     * @return array
     */
    public function defaultBlock (User $user)
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getUsername(),
            'introductions' => $user->getIntroductions(),
            'faction' => $user->getFaction(),
            'autoload_images' => $user->getAutoloadImages(),
            'donation' => $user->getDonation(),
            'unchecked_activity' => $this->activityHelper->countUncheckedItems($this->activityHelper->getItems($user)),
            'following' => array_map(function ($following) {
                        return $following->getId();
                    }, $user->getFollowing()->toArray())
        ];
    }

    public function decklistBlock (User $user, Decklist $decklist)
    {
        $dbh = $this->doctrine->getConnection();

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

        if ($this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
            $content['moderation_status'] = $decklist->getModerationStatus();
        }
        
        return $content;
    }

    public function cardBlock (User $user, Card $card)
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

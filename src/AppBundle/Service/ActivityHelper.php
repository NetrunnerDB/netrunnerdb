<?php

namespace AppBundle\Service;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Review;
use AppBundle\Entity\Reviewcomment;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ActivityHelper
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getItems(User $user, int $max_items = 30, int $nb_days = 7)
    {
        $following = $user->getFollowing();

        // don't return any items if the user follows nobody
        if(!count($following)) {
            return [];
        }

        $items = [];
        $last_activity_check = $user->getLastActivityCheck();

        // defining date limit
        $dateinf = new \DateTime();
        $dateinf->sub(new \DateInterval("P${nb_days}D"));

        // DECKLIST_PUBLISH
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('d');
        $qb->from('AppBundle:Decklist', 'd');
        $qb->where('d.dateCreation>:date');
        $qb->setParameter('date', $dateinf);
        $qb->andWhere('d.user in (:following)');
        $qb->setParameter('following', $following);
        $qb->setFirstResult(0);
        $qb->setMaxResults($max_items);

        $query = $qb->getQuery();
        /** @var Decklist $decklist */
        foreach ($query->getResult() as $decklist) {
            $items[] = [
                'type' => 'DECKLIST_PUBLISH',
                'date' => $decklist->getDateCreation(),
                'decklist' => $decklist,
                'unchecked' => $last_activity_check < $decklist->getDateCreation(),
            ];
        }

        // DECKLIST_COMMENT
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c');
        $qb->from('AppBundle:Comment', 'c');
        $qb->where('c.dateCreation>:date');
        $qb->setParameter('date', $dateinf);
        $qb->andWhere('c.author in (:following)');
        $qb->setParameter('following', $following);
        $qb->setFirstResult(0);
        $qb->setMaxResults($max_items);

        $query = $qb->getQuery();
        /** @var Comment $comment */
        foreach ($query->getResult() as $comment) {
            $items[] = [
                'type' => 'DECKLIST_COMMENT',
                'date' => $comment->getDateCreation(),
                'comment' => $comment,
                'unchecked' => $last_activity_check < $comment->getDateCreation(),
            ];
        }

        // REVIEW_PUBLISH
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r');
        $qb->from('AppBundle:Review', 'r');
        $qb->where('r.dateCreation>:date');
        $qb->setParameter('date', $dateinf);
        $qb->andWhere('r.user in (:following)');
        $qb->setParameter('following', $following);
        $qb->setFirstResult(0);
        $qb->setMaxResults($max_items);

        $query = $qb->getQuery();
        /** @var Review $review */
        foreach ($query->getResult() as $review) {
            $items[] = [
                'type' => 'REVIEW_PUBLISH',
                'date' => $review->getDateCreation(),
                'review' => $review,
                'unchecked' => $last_activity_check < $review->getDateCreation(),
            ];
        }

        // REVIEW_COMMENT
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c');
        $qb->from('AppBundle:Reviewcomment', 'c');
        $qb->where('c.dateCreation>:date');
        $qb->setParameter('date', $dateinf);
        $qb->andWhere('c.author in (:following)');
        $qb->setParameter('following', $following);
        $qb->setFirstResult(0);
        $qb->setMaxResults($max_items);

        $query = $qb->getQuery();
        /** @var Reviewcomment $reviewcomment */
        foreach ($query->getResult() as $reviewcomment) {
            $items[] = [
                'type' => 'REVIEW_COMMENT',
                'date' => $reviewcomment->getDateCreation(),
                'reviewcomment' => $reviewcomment,
                'unchecked' => $last_activity_check < $reviewcomment->getDateCreation(),
            ];
        }

        return $items;
    }

    public function countUncheckedItems(array $items)
    {
        $count = 0;
        foreach ($items as $item) {
            if ($item['unchecked']) {
                $count++;
            }
        }
        return $count;
    }

    public function sortByDay(array $items)
    {
        usort($items, function ($a, $b) {
            return $a['date'] < $b['date'] ? 1 : -1;
        });

        $items_by_day = [];
        foreach ($items as $item) {
            /** @var \DateTime $date */
            $date = $item['date'];
            $day = $date->format('F j, Y');
            $isoday = $date->format('Y-m-d');
            if (!key_exists($isoday, $items_by_day)) {
                $items_by_day[$isoday] = ['day' => $day, 'items' => []];
            }
            $items_by_day[$isoday]['items'][] = $item;
        }
        krsort($items_by_day);

        return $items_by_day;
    }
}

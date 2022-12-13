<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Display a summary of users with no decks or decklists, but reviews, review comments or card comments.
 * This helps identify spammy users. 
 */
class CommentOnlyUserSummaryCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
                ->setName('app:comment_only_user_summary')
                ->setDescription('Print out a summary for comment-only users to aid in spammy user detection.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
         $sql = <<<SQL
            SELECT
                u.username,
                u.email,
                COALESCE(num_comments.cnt, 0) as num_comments,
                COALESCE(num_comments.most_recent, '') as most_recent_comment_date,
                COALESCE(num_comments.days_active, '') as comment_days_active,
                COALESCE(num_reviews.cnt, 0) as num_reviews,
                COALESCE(num_reviews.most_recent, '') as most_recent_review_date,
                COALESCE(num_reviews.days_active, '') as review_days_active,
                COALESCE(num_reviewcomments.cnt, 0) as num_reviewcomments,
                COALESCE(num_reviewcomments.most_recent, '') as most_recent_reviewcomment_date,
                COALESCE(num_reviewcomments.days_active, '') as reviewcomment_days_active
            FROM
                user u
                LEFT JOIN (SELECT user_id, COUNT(*) as cnt FROM deck GROUP BY 1) num_decks ON u.id = num_decks.user_id
                LEFT JOIN (SELECT user_id, COUNT(*) as cnt FROM decklist GROUP BY 1) num_decklists ON u.id = num_decklists.user_id
                LEFT JOIN (SELECT user_id, COUNT(*) as cnt, MAX(date_creation) as most_recent, DATEDIFF(MAX(date_creation), MIN(date_creation)) as days_active FROM comment GROUP BY 1) num_comments ON u.id = num_comments.user_id
                LEFT JOIN (SELECT user_id, COUNT(*) as cnt, MAX(date_creation) as most_recent, DATEDIFF(MAX(date_creation), MIN(date_creation)) as days_active FROM review GROUP BY 1) num_reviews ON u.id = num_reviews.user_id
                LEFT JOIN (SELECT user_id, COUNT(*) as cnt, MAX(date_creation) as most_recent, DATEDIFF(MAX(date_creation), MIN(date_creation)) as days_active FROM reviewcomment GROUP BY 1) num_reviewcomments ON u.id = num_reviewcomments.user_id
            WHERE
                num_decks.cnt IS NULL AND num_decklists.cnt IS NULL
                AND (
                    num_comments.cnt IS NOT NULL
                    OR num_reviews.cnt IS NOT NULL
                    OR num_reviewcomments.cnt IS NOT NULL
                )
            ORDER BY
                GREATEST(COALESCE(num_comments.most_recent, ''), COALESCE(num_reviews.most_recent, ''), COALESCE(num_reviewcomments.most_recent, ''))
            SQL;

        $dbh = $this->entityManager->getConnection();
        $query = $dbh->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($query as $row) {
            $output->writeln("========================================================================");
            $output->writeln("user:                           {$row['username']}");
            $output->writeln("email:                          {$row['email']}");
            $output->writeln("num_comments:                   {$row['num_comments']}");
            $output->writeln("most_recent_comment_date:       {$row['most_recent_comment_date']}");
            $output->writeln("comment_days_active:            {$row['comment_days_active']}");
            $output->writeln("num_reviews:                    {$row['num_reviews']}");
            $output->writeln("most_recent_review_date:        {$row['most_recent_review_date']}");
            $output->writeln("review_days_active:             {$row['review_days_active']}");
            $output->writeln("num_reviewcomments:             {$row['num_reviewcomments']}");
            $output->writeln("most_recent_reviewcomment_date: {$row['most_recent_reviewcomment_date']}");
            $output->writeln("reviewcomment_days_active:      {$row['reviewcomment_days_active']}");
            $output->writeln("========================================================================");
        }
    }
}

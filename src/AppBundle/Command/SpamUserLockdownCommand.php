<?php

namespace AppBundle\Command;

use AppBundle\Entity\Review;
use AppBundle\Entity\Reviewcomment;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SpamUserLockdownCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    private $router;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    protected function configure()
    {
        $this
        ->setName('app:spam_user_lockdown')
        ->setDescription('Print out a summary of user decks, decklists, reviews, and comments.')
        ->addArgument(
            'username',
            InputArgument::REQUIRED,
            'Username to summarize.')
        ->addArgument(
            'confirmation',
            InputArgument::OPTIONAL,
            'specify "cerebral overwriter" to confirm that you wish to delete all comments, review comments, and reviews for the given user.'
        )
        ->addArgument(
            'url_prefix',
            InputArgument::OPTIONAL,
            'Username to summarize.',
            'https://netrunnerdb.com'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $confirmation = $input->getArgument('confirmation');
        $userEntity = $this->entityManager->getRepository('AppBundle:User');
        $url_prefix = $input->getArgument('url_prefix');
        $user = $userEntity->findOneBy(['username' => $username]);
        if (!($user instanceof User)) {
            $output->writeln('Could not find user ' . $username);
            return;
        }
        if ($confirmation != "cerebral overwriter") {
            $output->writeln("The confirmation phrase was not properly provided.  Exiting.");
            return;
        }

        $output-> writeln('===== User ==============');
        $output->writeln('User:');
        $output->writeln("  username: {$user->getUsername()}");
        $output->writeln("  email: {$user->getEmail()}");
        $output->writeln("  last_login: {$user->getLastLogin()->format('Y-m-d H:i:s')}");
        $output->writeln("  soft_ban: {$user->getSoftBan()}");
        $output->writeln('===================');

#        $output-> writeln('===== Decks =============');
#        $decks = $user->getDecks();
#        $output->writeln("  # decks: {$decks->count()}");
#        foreach ($decks as $deck) {
#          $output->writeln("   Deck {$deck->getId()}: {$deck->getName()}");
#        }
#
#        $output-> writeln('===== Decklists =========');
#        $decklists = $user->getDeckLists();
#        $output->writeln("  # decklists: {$decklists->count()}");
#
        $output-> writeln('===== Comments =========');
        $commentEntity = $this->entityManager->getRepository('AppBundle:Comment');
        $comments = $commentEntity->findBy(['author' => $user]);
        $num_comments = count($comments);
        $output->writeln("  # comments: {$num_comments}");
        foreach ($comments as $comment) {
            $decklist = $comment->getDecklist();
            $url = $this->router->generate('decklist_view', ['decklist_uuid' => $decklist->getUuid()]);
            $output->writeln("    Comment on {$decklist->getName()} ({$decklist->getUuid()}) {$url_prefix}{$url}\n{$comment->getText()}\n");
            $this->entityManager->remove($comment);
        }
        $this->entityManager->flush();

        $output-> writeln('===== Review Comments ===');
        $reviewCommentEntity = $this->entityManager->getRepository('AppBundle:Reviewcomment');
        $reviewComments = $reviewCommentEntity->findBy(['author' => $user]);
        $num_reviewComments = count($reviewComments);
        $output->writeln("  # reviewComments: {$num_reviewComments}");
        foreach ($reviewComments as $reviewComment) {
            $card = $reviewComment->getReview()->getCard();
            $url = $this->router->generate('cards_zoom', ['card_code' => $card->getCode()]);
            $output->writeln("    Review comment on {$card->getTitle()} ({$card->getCode()}) {$url_prefix}{$url}\n{$reviewComment->getText()}\n");
            $this->entityManager->remove($reviewComment);
        }
        $this->entityManager->flush();

        $output-> writeln('===== Reviews ===========');
        $reviews = $user->getReviews();
        $output->writeln("  # reviews: {$reviews->count()}");
        foreach ($reviews as $review) {
            $card = $review->getCard();
            $url = $this->router->generate('cards_zoom', ['card_code' => $card->getCode()]);
            $output->writeln("    Review on {$card->getTitle()} ({$card->getCode()}) {$url_prefix}{$url}\n{$review->getText()}\n");
            foreach ($review->getVotes() as $vote) {
                $this->entityManager->remove($vote);
            }
            foreach ($review->getComments() as $comment) {
                $this->entityManager->remove($comment);
            }
            $this->entityManager->remove($review);
        }
        $this->entityManager->flush();

        // Reset the password and change the email address for the account.
        $user->setEmail($user->getEmail() . "-was-a-damn-dirty-spammer");
        $user->setPassword('do not be a damn dirty spammer');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln("\n\n================================");
        $output->writeln("User Summary for {$username}:");
        $output->writeln("  username: {$user->getUsername()}");
        $output->writeln("  email: {$user->getEmail()}");
        $output->writeln("  last_login: {$user->getLastLogin()->format('Y-m-d H:i:s')}");
        $output->writeln("  soft_ban: {$user->getSoftBan()}");
        $output->writeln("  # comments: {$num_comments}");
        $output->writeln("  # reviews: {$reviews->count()}");
        $output->writeln("  # reviewComments: {$num_reviewComments}");
    }
}

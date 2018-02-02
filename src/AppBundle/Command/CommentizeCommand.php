<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Entity\Review;
use AppBundle\Entity\Reviewcomment;

class CommentizeCommand extends ContainerAwareCommand
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
        ->setName('app:commentize')
        ->setDescription('Turn a review into a comment')
        ->addArgument(
            'review_orig_id',
            InputArgument::REQUIRED,
            'Id of review to turn into a comment'
        )
        ->addArgument(
                'review_dest_id',
                InputArgument::REQUIRED,
                'Id of review to attach the comment to'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $review_orig_id = $input->getArgument('review_orig_id');
        $review_dest_id = $input->getArgument('review_dest_id');
        $error = $this->review_to_comment($review_orig_id, $review_dest_id);
        $output->writeln(date('c') . " " . (empty($error) ? "Success" : $error));
    }
    
    private function review_to_comment(int $review_orig_id, int $review_dest_id)
    {
        $repo = $this->entityManager->getRepository('AppBundle:Review');

        /** @var Review $review_orig */
        $review_orig = $repo->find($review_orig_id);

        /** @var Review $review_dest */
        $review_dest = $repo->find($review_dest_id);
        
        if (!$review_orig) {
            return "Review does not exist";
        }
        if (!$review_dest) {
            return "Dest review does not exist";
        }
        if (count($review_orig->getComments())) {
            return "Review has comments";
        }
        
        $text = $review_orig->getText();
        $text = strip_tags($text);
        
        $comment = new Reviewcomment();
        $comment->setAuthor($review_orig->getUser());
        $comment->setDatecreation($review_orig->getDateCreation());
        $comment->setDateupdate($review_orig->getDateupdate());
        $comment->setReview($review_dest);
        $comment->setText($text);
        $this->entityManager->persist($comment);
        $this->entityManager->remove($review_orig);
        $this->entityManager->flush();

        return null;
    }
}

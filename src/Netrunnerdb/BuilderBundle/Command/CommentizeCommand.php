<?php

namespace Netrunnerdb\BuilderBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Netrunnerdb\BuilderBundle\Entity\Review;
use Netrunnerdb\BuilderBundle\Entity\Reviewcomment;

class CommentizeCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this
        ->setName('nrdb:commentize')
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
    
    private function review_to_comment($review_orig_id, $review_dest_id)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /* @var $repo \Netrunnerdb\BuilderBundle\Entity\ReviewRepository */
        $repo = $em->getRepository('NetrunnerdbBuilderBundle:Review');
        /* @var $review_orig Review */
        $review_orig = $repo->find($review_orig_id);
        /* @var $review_dest Review */
        $review_dest = $repo->find($review_dest_id);
        
        if(!$review_orig) return "Review does not exist";
        if(!$review_dest) return "Dest review does not exist";
        if(count($review_orig->getComments())) return "Review has comments";
        
        $text = $review_orig->getText();
        $text = strip_tags($text);
        
        $comment = new Reviewcomment();
        $comment->setAuthor($review_orig->getUser());
        $comment->setDatecreation($review_orig->getDatecreation());
        $comment->setDateupdate($review_orig->getDateupdate());
        $comment->setReview($review_dest);
        $comment->setText($text);
        $em->persist($comment);
        $em->remove($review_orig);
        $em->flush();
        return;
    }
}
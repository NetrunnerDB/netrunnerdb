<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class EmailCommand extends ContainerAwareCommand
{

    protected function createArchiveFile($em, $user)
    {
        $decks = $this->getContainer()->get('decks')->getByUser($user);
        if(!count($decks)) return FALSE;
        
        $file = tempnam("tmp", "zip");
        $zip = new \ZipArchive();
        $res = $zip->open($file, \ZipArchive::OVERWRITE);
        if ($res === TRUE)
        {
            foreach($decks as $deck)
            {
                $content = array();
                foreach($deck['cards'] as $slot)
                {
                    $card = $em->getRepository('AppBundle:Card')->findOneBy(array('code' => $slot['card_code']));
                    if(!$card) continue;
                    $cardtitle = $card->getTitle();
                    $packname = $card->getPack()->getName();
                    if($packname == 'Core Set') $packname = 'Core';
                    $qty = $slot['qty'];
                    $content[] = "$cardtitle ($packname) x$qty";
                    $em->detach($card);
                }
                $filename = str_replace('/', ' ', $deck['name']).'.txt';
                $zip->addFromString($filename, implode("\r\n", $content));
            }
            $res = $zip->close();
        }
        
        if($res === TRUE) return $file;
        else return FALSE;
    }
    
    /**
     * @param $user \AppBundle\Entity\User
     */
    protected function sendArchive($em, $user, $path)
    {
        $message = \Swift_Message::newInstance();
        
        // Give the message a subject
        $message->setSubject('Your decks on NetrunnerDB');
        
        // Set the From address with an associative array
        $message->setFrom(array('alsciende@icloud.com' => 'Alsciende'));
        
        // Set the To addresses with an associative array
        $message->setTo(array($user->getEmail() => $user->getUsername()));
        
        // Give it a body
        $message->setBody($this->getContainer()->get('templating')->render('/Emails/deck_archive.txt.twig', array(
        	'username' => $user->getUsername()
        )));
        
        // And optionally an alternative body
        $message->addPart($this->getContainer()->get('templating')->render('/Emails/deck_archive.html.twig', array(
        	'username' => $user->getUsername()
        )), 'text/html');
        
        $attachment = \Swift_Attachment::fromPath($path, 'application/zip')->setFilename('netrunnerdb.zip');
        $message->attach($attachment);

        $this->getContainer()->get('mailer')->send($message);
    }
    
    protected function configure()
    {
        $this
        ->setName('nrdb:email')
        ->setDescription('Send archive to one user or all users')
        ->addArgument(
            'user_id',
            InputArgument::OPTIONAL,
            'User to send archive to'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user_id = $input->getArgument('user_id');
        $users = array();
        
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        if($user_id) {
            $users[] = $em->getRepository('AppBundle:User')->find($user_id);
        } else {
            $users = $em->getRepository('AppBundle:User')->findBy(array('locked' => FALSE));
        }
        $nb=3;
        foreach($users as $user) {
            $output->writeln($user->getUsername());
            $path = $this->createArchiveFile($em, $user);
            if($path !== FALSE) {
                $this->sendArchive($em, $user, $path);
                $output->writeln("  Sent.");
            }
            $user->setLocked(TRUE);
            $em->flush();
            $output->writeln("  Locked.");
            $em->detach($user);
            if($nb-- == 0) break;
        }
    }
}

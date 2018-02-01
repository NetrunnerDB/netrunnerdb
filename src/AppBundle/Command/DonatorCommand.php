<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DonatorCommand extends ContainerAwareCommand
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
            ->setName('nrdb:donator')
            ->setDescription('Add a donation to a user by email address or username')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email address or username of user'
            )
            ->addArgument(
                'donation',
                InputArgument::REQUIRED,
                'Amount of donation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $donation = $input->getArgument('donation');

        $repo = $this->entityManager->getRepository('AppBundle:User');
        /* @var $user \AppBundle\Entity\User */
        $user = $repo->findOneBy(['email' => $email]);
        if (!$user instanceof User) {
            $user = $repo->findOneBy(['username' => $email]);
        }

        if ($user instanceof User) {
            $user->setDonation($donation + $user->getDonation());
            $this->entityManager->flush();
            $output->writeln(date('c') . " " . "Success");
        } else {
            $output->writeln(date('c') . " " . "Cannot find user [$email]");
        }
    }
}

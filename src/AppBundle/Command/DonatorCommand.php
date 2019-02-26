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
            ->setName('app:donator')
            ->setDescription('Add a donation to a user by email address or username')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email address or username of user'
            )
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                '"paypal" or "patreon"'
            )
            ->addArgument(
                'amount',
                InputArgument::REQUIRED,
                'Amount of donation in US dollars'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $type = $input->getArgument('type');
        $amount = $input->getArgument('amount');

        $repo = $this->entityManager->getRepository('AppBundle:User');
        /** @var User $user */
        $user = $repo->findOneBy(['email' => $email]);
        if (!$user instanceof User) {
            $user = $repo->findOneBy(['username' => $email]);
        }

        if ($type == 'patreon' or $type == 'paypal') {
            if ($user instanceof User) {
                if ($type == 'paypal') {
                    $user->setDonation($amount + $user->getDonation());
                } else {
                    $user->setPatreonPledgeCents($amount * 100);
                }
                $this->entityManager->flush();
                $output->writeln(date('c') . " " . "Success");
            } else {
                $output->writeln(date('c') . " " . "Cannot find user [$email]");
            }
        } else {
            $output->writeln(date('c') . " " . "Invalid donation type [$type]: expected 'patreon' or 'paypal'");
        }
    }
}

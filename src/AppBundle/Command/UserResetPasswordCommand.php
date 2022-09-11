<?php

namespace AppBundle\Command;

use FOS\UserBundle\Util\UserManipulator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class UserResetPasswordCommand extends ContainerAwareCommand
{
    private $userManipulator;

    public function __construct(UserManipulator $userManipulator)
    {
        parent::__construct();

        $this->userManipulator = $userManipulator;
    }

    protected function configure()
    {
        $this
                ->setName('app:user:reset-password')
                ->setDescription("Resets a user's password")
                ->addArgument(
                    'username',
                    InputArgument::REQUIRED,
                    "The user's username"
                )
                ->addArgument(
                    'password',
                    InputArgument::REQUIRED,
                    'The new password to give the user'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $this->userManipulator->changePassword($username, $password);

        $output->writeln(sprintf('Changed password for user <comment>%s</comment>', $username));
    }
}

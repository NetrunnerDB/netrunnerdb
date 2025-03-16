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

class RebirthCommand extends ContainerAwareCommand
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
        ->setName('app:rebirth')
        ->setDescription('Bring back a previously locked down user.')
        ->addArgument(
            'username',
            InputArgument::REQUIRED,
            'Username to summarize.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $userEntity = $this->entityManager->getRepository('AppBundle:User');
        $user = $userEntity->findOneBy(['username' => $username]);
        if (!($user instanceof User)) {
            $output->writeln('Could not find user ' . $username);
            return;
        }

        $output-> writeln('===== User ==============');
        $output->writeln('User:');
        $output->writeln("  username: {$user->getUsername()}");
        $output->writeln("  email: {$user->getEmail()}");
        $output->writeln("  last_login: {$user->getLastLogin()->format('Y-m-d H:i:s')}");
        $output->writeln('==========================');

        if (!strpos($user->getEmail(), "-was-a-damn-dirty-spammer")) {
            $output->writeln('User is already reborn.');
            return;
        }

        $user->setEmail(str_replace("-was-a-damn-dirty-spammer", "", $user->getEmail()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}

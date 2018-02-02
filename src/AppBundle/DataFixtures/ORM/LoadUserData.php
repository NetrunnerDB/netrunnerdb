<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of LoadUserData
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @var ContainerInterface|null
     */
    private $container;

    public function load(ObjectManager $manager)
    {
        if (!$this->container instanceof ContainerInterface) {
            throw new \Exception('No Container.');
        }

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        $userAdmin = $userManager->createUser();
        $userAdmin->setUsername('admin');
        $userAdmin->setEmail('admin@example.org');
        $userAdmin->setPlainPassword('admin');
        $userAdmin->addRole('ROLE_ADMIN');
        $userAdmin->setEnabled(true);
        $userManager->updateUser($userAdmin);
        $this->addReference('admin-user', $userAdmin);

        $userGuru = $userManager->createUser();
        $userGuru->setUsername('guru');
        $userGuru->setEmail('guru@example.org');
        $userGuru->setPlainPassword('guru');
        $userGuru->addRole('ROLE_GURU');
        $userGuru->setEnabled(true);
        $userManager->updateUser($userGuru);
        $this->addReference('guru-user', $userGuru);
    }

    public function getOrder()
    {
        return 1;
    }
}

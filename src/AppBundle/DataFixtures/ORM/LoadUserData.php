<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * Description of LoadUserData
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /** @var UserManagerInterface $userManager */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function load(ObjectManager $manager)
    {
        $userAdmin = $this->userManager->createUser();
        $userAdmin->setUsername('admin');
        $userAdmin->setEmail('admin@example.org');
        $userAdmin->setPlainPassword('admin');
        $userAdmin->addRole('ROLE_ADMIN');
        $userAdmin->setEnabled(true);
        $this->userManager->updateUser($userAdmin);
        $this->addReference('admin-user', $userAdmin);

        $userGuru = $this->userManager->createUser();
        $userGuru->setUsername('guru');
        $userGuru->setEmail('guru@example.org');
        $userGuru->setPlainPassword('guru');
        $userGuru->addRole('ROLE_GURU');
        $userGuru->setEnabled(true);
        $this->userManager->updateUser($userGuru);
        $this->addReference('guru-user', $userGuru);
    }

    public function getOrder()
    {
        return 1;
    }
}

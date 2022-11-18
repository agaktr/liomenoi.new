<?php

namespace App\DataFixtures;

use App\Entity\Apto\Notification;
use App\Entity\Apto\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public const SUPER_USER_REFERENCE = 'super-user';
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const USER_REFERENCE = 'user';
    public const NOTIFICATION_REFERENCE = 'norification';

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        //create a new super admin fixture
        $superAdmin = new User();
        $superAdmin->setUsername('super');
        $password = $this->hasher->hashPassword($superAdmin, 'apassword28');
        $superAdmin->setPassword($password);
        $superAdmin->setIsVerified(true);
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setEmail('super@admin.com');
        $superAdmin->setCreated(new DateTime());
        $superAdmin->setUpdated(new DateTime());

        $manager->persist($superAdmin);

        //create a new admin fixture
        $userAdmin = new User();
        $userAdmin->setUsername('admin');
        $password = $this->hasher->hashPassword($userAdmin, 'apassword28');
        $userAdmin->setPassword($password);
        $userAdmin->setIsVerified(true);
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setEmail('admin@admin.com');
        $userAdmin->setCreated(new DateTime());
        $userAdmin->setUpdated(new DateTime());

        $manager->persist($userAdmin);

        //create a new user fixture
        $user = new User();
        $user->setUsername('user');
        $password = $this->hasher->hashPassword($user, 'apassword28');
        $user->setPassword($password);
        $user->setIsVerified(true);
        $user->setEmail('user@user.com');
        $user->setCreated(new DateTime());
        $user->setUpdated(new DateTime());

        $manager->persist($user);

        //create a new notification fixture
        $notification = new Notification();
        $notification->setName('Test Notification');
        $notification->setContent('Test Content Notification');
        $notification->addUser($user);
        $notification->addUser($userAdmin);
        $notification->addUser($superAdmin);

        $manager->persist($notification);

        //save the fixtures
        $manager->flush();

        // other fixtures can get this object using the UserFixtures::{TheReference} constant
        $this->addReference(self::SUPER_USER_REFERENCE, $superAdmin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $userAdmin);
        $this->addReference(self::USER_REFERENCE, $user);
        $this->addReference(self::NOTIFICATION_REFERENCE, $notification);
    }
}

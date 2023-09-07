<?php

namespace App\DataFixtures;

use App\Entity\Location;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $userPasswordHasherInterface;

    public function __construct (UserPasswordHasherInterface $userPasswordHasherInterface) 
    {
        $this->hasher = $userPasswordHasherInterface;
    }

/* 
	name: load
	description: Creates an administrator account with the console command "php bin/console doctrine:fixtures:load"
*/	    
    public function load(ObjectManager $manager ): void
    {
        // preset admin
        $user = new User();
        $user->setName('Admin');
        $user->setCreationTime(new \Datetime('now'));
        $user->setEmail('i22almoj@uco.es');
        $user->setActive(true);
        $user->setPassword($this->hasher->hashPassword($user, 'admin.password'));
        $user->setRole('ROLE_ADMIN');
        $manager->persist($user);
        $manager->flush();
        echo "INFO: imported preset admin: \'admin\' pass: \'admin.password\' \n";
    }
}

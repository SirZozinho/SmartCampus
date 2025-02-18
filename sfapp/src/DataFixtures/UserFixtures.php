<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager):void {

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $plaintextPassword = "technician".$i;
            $user->setLogin("technician".$i);
            $user->setEmail("technician".$i."@example.com");
            $user->setRoles(["ROLE_USER"]);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $manager->persist($user);

        }

        $manager->flush();


    }

}
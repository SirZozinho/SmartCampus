<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @brief Creates admin user
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // ... e.g. get the user data from a registration form
        $admin = new User();
        $plaintextPassword = "admin";
        $admin->setLogin("admin");
        $admin->setEmail("admin@admin.com");
        $admin->setRoles(["ROLE_ADMIN"]);

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $plaintextPassword
        );
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        $user = new User();
        $plaintextPassword = "lucas";
        $user->setLogin("lucas");
        $user->setEmail("lucas@lucas.com");
        $user->setRoles(["ROLE_USER"]);

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $manager->flush();


    }
}

<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @brief Main constructor of that class
     * @param ManagerRegistry $registry
     * @author Louis PAQUEREAU
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @brief Used to upgrade (rehash) the user's password automatically over time.
     * @param PasswordAuthenticatedUserInterface $user The target user
     * @param string $newHashedPassword The new hashed password
     * @author Louis PAQUEREAU
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @brief Finds a user by their login.
     * @param string $searchTerm The login of the user.
     * @return array Returns a User object or null if no user is found.
     * @author Louis PAQUEREAU
     */
    public function findByLoginLike(string $searchTerm): array
    {
        //Return the result of the DQL request
        return $this->createQueryBuilder('u')
            ->andWhere('u.login LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }

}

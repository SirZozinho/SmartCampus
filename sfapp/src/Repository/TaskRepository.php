<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @brief Find a task by its exact name
     * @param string|null $name The target task
     * @return mixed return the target task if exists
     * @author Enzo BIGUET
     */
    public function findByName(?string $name)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name = :name')
            ->setParameter('name',  $name)
            ->getQuery()
            ->getResult();
    }

    /**
     * @brief get all priority tasks that exists
     * @return array all priority tasks that exists
     * @author Enzo BIGUET
     */
    public function getAllPriority(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.priority')
            ->orderBy('p.priority', 'ASC');

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * @brief get all tasks that exists
     * @return array all tasks that exists
     * @author Enzo BIGUET
     */
    public function getAllAdvancement(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.advancement')
            ->orderBy('p.advancement', 'ASC');

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    public function findByNameAndUser($name, $user)
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.label LIKE :name')
            ->setParameter('user', $user)
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function getAllUsers()
    {
        return $this->createQueryBuilder('u') // 'u' est l'alias pour User
        ->select('u')
            ->distinct()
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

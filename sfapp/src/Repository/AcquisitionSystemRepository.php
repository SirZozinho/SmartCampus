<?php

namespace App\Repository;

use App\Entity\AcquisitionSystem;
use App\Enum\AcquisitionSystemState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AcquisitionSystem>
 */
class AcquisitionSystemRepository extends ServiceEntityRepository
{
    /**
     * @brief main constructor of that class
     * @param ManagerRegistry $registry
     * @author Leonard LARDEUX
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcquisitionSystem::class);
    }

    /**
     * @brief get acquisition system by it's exact name
     * @param string $name name of acquisition system
     * @return AcquisitionSystem|null return the acquisition system if it exists
     */
    public function findByName(string $name): ?AcquisitionSystem
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @brief Search acquisition that matches with the query
     * @param string|null $query the input string
     * @return mixed return a list of acquisition system that matches wuth the query
     * @author Leonard LARDEUX
     */
    public function findByNameLike(?string $query)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :name')
            ->setParameter('name',  $query . '%') // Utilise les jokers '%' pour la recherche partielle
            ->getQuery()
            ->getResult();
    }

    /**
     * @brief find acquisition system by it's state
     * @param AcquisitionSystemState $state the selected acquisition system state
     * @return array return a list of acquisition system that matches with the input state
     * @author Louis PAQUEREAU
     */
    public function findByState(AcquisitionSystemState $state): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.state = :state')
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }
}

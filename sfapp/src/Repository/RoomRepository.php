<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     * @author Enzo BIGUET
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @brief Register a new room in the database
     * @param Request $request The input room
     * @param EntityManagerInterface $entityManager The entity manager to manage data
     * @return Response The state of the treatment of the request (if it's successful or not)
     * @tuhor Enzo BIGUET
     */
    public function addRoom(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();

        // Créer le formulaire à partir de RoomType
        $form = $this->createForm(RoomType::class, $room);

        // Traiter la requête si le formulaire est soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder la salle en base de données
            $entityManager->persist($room);
            $entityManager->flush();

            // Rediriger vers une autre page ou afficher un message de confirmation
            return $this->redirectToRoute('room_list');  // Remplace 'room_list' par la route souhaitée
        }

        return $this->render('rooms/roomsManagement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @brief Unregister an existing room from the database
     * @param int $roomId The ID of the existing room
     * @param EntityManagerInterface $entityManager The entity manager to manage data
     * @return void
     * @author Enzo BIGUET
     */
    public function deleteRoom(int $roomId, EntityManagerInterface $entityManager): void
    {
        // Chercher la salle à supprimer en utilisant son ID
        $room = $this->find($roomId);

        if ($room) {
            // Si la salle existe, on la supprime
            $entityManager->remove($room);
            $entityManager->flush();
        }
    }

    /**
     * @brief Find a room by it's state
     * @param string $state the input state
     * @return mixed a list or one room that matches with the input room state
     * @author Enzo BIGUET
     */
    public function findByState(string $state)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.state LIKE :state')
            ->setParameter('state',  $state) // Utilise les jokers '%' pour la recherche partielle
            ->getQuery()
            ->getResult();
    }

    /**
     * @brief Find a room by its exact name
     * @param string|null $name The target room
     * @return mixed return the target room if exists
     * @author Leonard LARDEUX
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
     * @brief Find rooms that matches with the query
     * @param string|null $query The input string
     * @return mixed return the list that matched with the query
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

    public function findByNameLikeAndState(?string $name, ?string $state)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :name')
            ->andWhere('a.state LIKE :state')
            ->setParameter('name',  $name . '%') // Utilise les jokers '%' pour la recherche partielle
            ->setParameter('state',  $state)
            ->getQuery()
            ->getResult();
    }

    /**
     * @brief get all floors that exists
     * @return array all floors that exists
     * @author Leonard LARDEUX
     */
    public function getAllFloors(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('DISTINCT r.floor')
            ->orderBy('r.floor', 'ASC');

        return array_column($qb->getQuery()->getArrayResult(), 'floor');
    }

    /**
     * @brief get floors that contains rooms
     * @return array return all floor that contains roomz
     * @author Leonard LARDEUX
     */
    public function getAvailableFloor(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('DISTINCT r.floor')
            ->where('r.state = :state')
            ->setParameter('state', 'ÉQUIPÉ')
            ->orderBy('r.floor', 'ASC');

        return array_column($qb->getQuery()->getArrayResult(), 'floor');
    }
}

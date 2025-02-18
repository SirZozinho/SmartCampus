<?php

namespace App\Controller;

use App\Entity\Room;
use App\Enum\RoomState;
use App\Form\RoomSAAssociationType;
use App\Form\RoomType;
use App\Repository\AcquisitionSystemRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomsController extends AbstractController
{
    /**
     * @brief Show room management interface
     * @param RoomRepository $roomRepository The rooms container
     * @param Request $request The request sent by the search bar
     * @param EntityManagerInterface $entityManager The entity manager
     * @return Response returns the view
     * @author Enzo BIGUET
     */
    #[Route('/rooms', name: 'app_room')]
    public function index(RoomRepository $roomRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $searchTerm = $request->query->get('name'); // Récupère le terme de recherche depuis l'URL
        $floorFilter = $request->query->get('floor', 'all'); // Étages à filtrer
        $stateFilter = $request->query->get('state', 'all'); // État à filtrer

        if ($searchTerm) {
            // Si un terme de recherche est fourni, utilise la méthode findByFloorAndName pour rechercher une salle par son nom
            $rooms = $roomRepository->findByNameLike($searchTerm);
        } else {
            // Si aucun terme de recherche, récupère toutes les salles et les affiches par étage et par ordre alphabétique
            $rooms = $roomRepository->findBy([], ['floor' => 'ASC', 'name' => 'ASC']);
        }

        // Application du filtre d'étage sur les résultats
        if ($floorFilter && $floorFilter !== 'all') {
            $filteredRooms = [];
            foreach ($rooms as $room) {
                if ($floorFilter === 'Rez-de-chaussée' && $room->getFloor() === 0) {
                    $filteredRooms[] = $room;
                } elseif ((int)$floorFilter === $room->getFloor()) {
                    $filteredRooms[] = $room;
                }
            }
            $rooms = $filteredRooms;
        }

        // Application du filtre d'état sur les résultats
        if ($stateFilter && $stateFilter !== 'all') {
            $filteredRooms = [];
            foreach ($rooms as $room) {
                if ($room->getState()?->value == $stateFilter) {
                    $filteredRooms[] = $room;
                }
            }
            $rooms = $filteredRooms;
        }

        $allFloors = $roomRepository->getAllFloors();
        $allStates = ['DISPONIBLE', 'INDISPONIBLE', 'ÉQUIPÉ'];


        return $this->render('rooms/roomsManagement.html.twig', [
            'rooms' => $rooms, // Envoie les systèmes récupérés à la vue
            'isSearch' => (bool)$searchTerm, // indique si cette page est utilisée pour la recherche
            'searchTerm' => $searchTerm, // terme de recherche pour pré-remplir le champ dans le formulaire
            'floorFilter' => $floorFilter,
            'stateFilter' => $stateFilter,
            'allFloors' => $allFloors,
            'allStates' => $allStates,

        ]);
    }

    /**
     * @brief Add room interface
     * @param RoomRepository $roomRepository The room container
     * @param Request $request The request sent by the form
     * @param EntityManagerInterface $entityManager The entity manager
     * @return Response return the view
     * @author Enzo BIGUET
     */
    #[Route('/rooms/add', name: 'app_add_room')]
    public function addRoom(RoomRepository $roomRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $rooms = $roomRepository->findAll();

        // Créer le formulaire pour ajouter une nouvelle salle
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        $sent = false;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($room);
                $entityManager->flush();
                $sent = true;
                return $this->redirectToRoute('app_room');
            } catch (UniqueConstraintViolationException $e) {
                //enregistre un message d'erreur si une contrainte d'unicité est violée
                $error = "Cette salle existe déjà.";
            }
        }


        // Rendre la vue avec la liste des salles et le formulaire
        return $this->render('rooms/roomsAdd.html.twig', [
            'text' => $sent,
            'form' => $form->createView(), // Utilise createView() pour envoyer la vue du formulaire
            'error' => $error,
        ]);
    }

    /**
     * @brief Delete room (no interface, just a redirection)
     * @param int $id The target room (represented by its ID)
     * @param Request $request The request sent to remove the room
     * @param RoomRepository $roomRepository The room container
     * @param EntityManagerInterface $entityManager The entity manager
     * @return Response
     */
    #[Route('/rooms/delete/{id}', name: 'app_delete_room', methods: ['POST'])]
    public function deleteRoom(int $id, Request $request, RoomRepository $roomRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Rechercher la salle par son ID
        $room = $roomRepository->find($id);

        if ($room) {
            // Supprimer la salle
            $entityManager->remove($room);
            $entityManager->flush();
            $this->addFlash('success', 'La salle a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Salle introuvable.');
        }

        // Rediriger vers la liste des salles
        return $this->redirectToRoute('app_room');
    }

    #[Route('/rooms/modify/{id}', name: 'app_modify_room')]
    public function modifyRoom(int $id, Request $request, RoomRepository $repository, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer la salle par ID
        $room = $repository->find($id);

        // Vérifie si la salle existe
        if (!$room) {
            throw $this->createNotFoundException('Salle non trouvée');

        }

        // Créer le formulaire en liant l'objet room
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        $sent = false;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manager->flush();  // Enregistre les modifications
                $sent = true;
                // Redirige vers la page de liste ou une autre page après modification
                return $this->redirectToRoute('app_room');
            } catch (UniqueConstraintViolationException $e) {
                $error = "Cette salle existe déjà.";
            }
        }


        // Afficher le formulaire de modification
        return $this->render('rooms/roomsModify.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'text' => $sent,
        ]);
    }

    /**
     * @brief Associate a room to an acquisition system
     * @param int $roomId the target room
     * @param RoomRepository $roomRepo The rooms container
     * @param Request $request The request that contains the target acquisition system
     * @param EntityManagerInterface $manager The entity manager interface to apply modifications
     * @return Response returns the view
     * @author Louis PAQUEREAU
     */
    #[Route('/rooms/associate/{roomId}', name: 'app_associateSAToRoom')]
    public function associate(int $roomId, RoomRepository $roomRepo, Request $request,  EntityManagerInterface $manager): Response
    {

        /*
         * Get information
         *
         * - If the user is the manager
         * - If the room is found
         */
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); //Defines that only users that has role admin is permitted to access to that page


        $targetRoom = $roomRepo->find($roomId); //Defines the target room instance returned by the roomRepository
        $error = null; //Defines the incoming errors. It is shown on the interface


        /* Checking integrity
         *
         * -> We cannot associate a room that is already associated
         * -> We cannot associate a room that doesn't exist
         */

        //Checks if target room is not null before redirecting
        if (!$targetRoom) {
            return $this->redirectToRoute('app_room');
        }

        //Checks if target room state is available for association
        if ($targetRoom->getState() == RoomState::UNAVAILABLE || $targetRoom->getState() == RoomState::EQUIPED) {
            return $this->redirectToRoute('app_room');
        }


        $form = $this->createForm(RoomSaAssociationType::class, $targetRoom); //Creates the form

        /*
         * Applies modification
         */
        $form->handleRequest($request); //When user click on 'confirm' button. This method catch request

        //Check if returned values comply to the form
        if ($form->isSubmitted() && $form->isValid()) {

            //Try updating values
            //If the name is the associated acquisition system is the same as another in a registered room,
            //that raise an error and cancel the modification.
            try {
                //Update database
                $targetRoom->setState(RoomState::EQUIPED);
                $manager->flush();
                return $this->redirectToRoute('app_room');

            } catch (UniqueConstraintViolationException $e) {
                $error = "Vous ne pouvez pas associer ce système d'acquisition dans une autre salle";

            }
        }

        /*
         * Show interface
         */
        return $this->render('rooms/roomsAssociate.html.twig', [
            'controller_name' => 'RoomSaAssociationController',
            'targetRoom' => $targetRoom,
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route("/rooms/dissociate/{roomId}", name: 'app_dissociateSAToRoom')]
    public function disassociate(int $roomId,Request $request, RoomRepository $roomRepository, EntityManagerInterface $manager): Response
    {
        /*
         * Get information
         *
         * - If the user is the manager
         * - If the room is found
         */

        $this->denyAccessUnlessGranted('ROLE_ADMIN'); //Defines that only users that has role admin is permitted to access to that page

        $room = $roomRepository->find($roomId); //Defines the target room instance returned by the roomRepository

        /* Dissociate the acquisition system from this room
         *
         * - We need that room exists
         */
        if ($room) {
            //Update database
            $room->setAcquisitionSystem(null);
            $room->setState(RoomState::AVAILABLE);
            $manager->flush();
            $this->addFlash('success', 'La salle a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Salle introuvable.');
        }

        /*
         * Show interface by redirecting to the room management
         */
        return $this->redirectToRoute('app_room');
    }
}


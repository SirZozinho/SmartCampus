<?php
namespace App\Controller;

use App\Entity\Task;
use App\Enum\TaskPriorityState;
use App\Enum\TaskState;
use App\Form\AddTaskType;
use App\Repository\RoomRepository;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskManagementController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/tasks', name: 'app_task_management')]
    public function index(TaskRepository $taskRepository, Request $request): Response
    {
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération des filtres depuis l'URL
        $searchTerm = $request->query->get('name');
        $priorityStateFilter = $request->query->get('priority', 'all');
        $advancementFilter = $request->query->get('advancement', 'all');
        $userFilter = $request->query->get('user', 'all'); // Filtre pour l'utilisateur

        // Requête de base selon le rôle
        $tasks = $isAdmin
            ? ($searchTerm ? $taskRepository->findByName($searchTerm) : $taskRepository->findAll())
            : ($searchTerm ? $taskRepository->findByNameAndUser($searchTerm, $user) : $taskRepository->findBy(['user' => $user]));

        $allTasks = $taskRepository->findAll();

        // Filtrage par priorité
        if ($priorityStateFilter && $priorityStateFilter !== 'all') {
            $tasks = array_filter($tasks, fn($task) => (string)$task->getPriority() === $priorityStateFilter);
        }

        // Filtrage par avancement
        if ($advancementFilter && $advancementFilter !== 'all') {
            $tasks = array_filter($tasks, fn($task) => (string)$task->getState() === $advancementFilter);
        }

        // Filtrage par utilisateur (admin uniquement)
        if ($isAdmin && $userFilter && $userFilter !== 'all') {
            $tasks = array_filter($tasks, fn($task) => $task->getUser() && $task->getUser()->getId() == $userFilter);
        }

        // Récupérer toutes les priorités, états d'avancement, et utilisateurs
        $allPriority = $taskRepository->getAllPriority();
        $allAdvancement = $taskRepository->getAllAdvancement();
        $allUsers = $taskRepository->getAllUsers();

        return $this->render('task_management/tasks.html.twig', [
            'tasks' => $tasks,
            'isSearch' => (bool)$searchTerm,
            'searchTerm' => $searchTerm,
            'priorityStateFilter' => $priorityStateFilter,
            'advancementFilter' => $advancementFilter,
            'userFilter' => $userFilter, // Ajout du filtre utilisateur
            'allPriority' => $allPriority,
            'allAdvancement' => $allAdvancement,
            'allUsers' => $allUsers, // Liste des utilisateurs pour le filtre
            'allTasks' => $allTasks,
        ]);
    }


    #[Route('/tasks/add', name: 'app_add_task')]
    public function addTask(TaskRepository $taskRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $task = new Task();
        $form = $this->createForm(AddTaskType::class, $task);

        $form->handleRequest($request);
        $sent = false;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($task->getUser() === null && $task->getAdvancement() != 'NON-ASSOCIÉ') {
                    // Lancer une exception personnalisée si aucun technicien n'est associé
                    throw new \Exception("Une tâche ne peut pas être avancée si elle n'est associée à aucun technicien.");
                }

                $this->entityManager->persist($task);
                $this->entityManager->flush();
                $sent = true;
                return $this->redirectToRoute('app_task_management');
            } catch (\Exception $e) {
                // Capture de l'exception personnalisée et affichage du message d'erreur
                $error = $e->getMessage();
            }
        }

        return $this->render('task_management/tasksAdd.html.twig', [
            'form' => $form->createView(),
            'text' => $sent,
            'error' => $error,
        ]);
    }

    #[Route('/tasks/modify/{id}', name: 'app_modify_task')]
    public function modifyTask(int $id, Request $request, TaskRepository $repository, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer la salle par ID
        $task = $repository->find($id);

        // Vérifie si la salle existe
        if (!$task) {
            throw $this->createNotFoundException('Tache non trouvée');
        }

        // Créer le formulaire en liant l'objet room
        $form = $this->createForm(AddTaskType::class, $task);
        $form->handleRequest($request);

        $sent = false;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manager->flush();  // Enregistre les modifications
                $sent = true;
                // Redirige vers la page de liste ou une autre page après modification
                return $this->redirectToRoute('app_task_management');
            } catch (UniqueConstraintViolationException $e) {
                $error = "Cette tache existe déjà.";
            }
        }

        // Afficher le formulaire de modification
        return $this->render('task_management/task_modify.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'text' => $sent,
        ]);
    }


     #[Route("/task/{id}/mark_as_completed", name: 'app_mark_task_as_completed')]
     public function markTaskAsCompleted(int $id, TaskRepository $taskRepository, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupérer la tâche par son ID
        $task = $taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('La tâche n\'existe pas.');
        }

        // Mettre à jour l'état de la tâche à "TERMINÉ"
        $task->setAdvancement(TaskState::COMPLETED);

        // Sauvegarder les changements en base de données
        $manager->flush();

        // Rediriger vers la page de gestion des tâches avec un message flash de succès
        $this->addFlash('success', 'La tâche a été marquée comme terminée.');
        return $this->redirectToRoute('app_task_management');
    }

    #[Route('/tasks/delete/{id}', name: 'app_delete_task', methods: ['POST'])]
    public function deleteTask(int $id, Request $request, TaskRepository $taskRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Rechercher la salle par son ID
        $task = $taskRepository->find($id);

        if ($task) {
            // Supprimer la salle
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tache a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Tache introuvable.');
        }

        // Rediriger vers la liste des salles
        return $this->redirectToRoute('app_task_management');
    }
}
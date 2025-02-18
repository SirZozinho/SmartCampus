<?php

namespace App\Controller;

use App\Enum\AcquisitionSystemState;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AcquisitionSystem;
use App\Form\AcquisitionSystemType;
use App\Repository\AcquisitionSystemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AcquisitionSystemController extends AbstractController
{
    /**
     * @author Léonard Lardeux
     * @brief Handles acquisition system management page rendering.
     * @param Request $request HTTP request object containing query parameters.
     * @param AcquisitionSystemRepository $repository Repository to fetch acquisition systems.
     * @return Response Renders the management page view.
     */
    #[Route('/acquisitionsystem', name: 'app_acquisition_system')]
    public function index(Request $request, AcquisitionSystemRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER'); // Ensure the user has appropriate permissions

        $searchTerm = $request->query->get('name'); // Retrieve the search term from the query
        $stateFilter = $request->query->get('state', 'all'); // Retrieve the state filter, default to 'all'

        // Fetch acquisition systems based on search term and state filter
        $systems = $this->fetchAcquisitionSystems($repository, $searchTerm, $stateFilter);

        // Render the management page with the filtered systems
        return $this->render('acquisition_system/asManagement.html.twig', [
            'systems' => $systems,
            'isSearch' => (bool)$searchTerm,
            'searchTerm' => $searchTerm,
            'stateFilter' => $stateFilter,
            'allStates' => ['FONCTIONNEL', 'MAINTENANCE', 'NON-ASSOCIÉ', 'DÉFAILLANT', 'HORS-SERVICE'],
        ]);
    }

    /**
     * @author Léonard Lardeux
     * @brief Fetches acquisition systems based on search term and state filter.
     * @param AcquisitionSystemRepository $repository Repository to fetch acquisition systems.
     * @param string|null $searchTerm Optional search term to filter systems by name.
     * @param string $stateFilter State filter for acquisition systems.
     * @return array Filtered acquisition systems.
     */
    private function fetchAcquisitionSystems(AcquisitionSystemRepository $repository, ?string $searchTerm, string $stateFilter): array
    {
        // If search term is provided, find systems by name, otherwise fetch all systems
        $systems = $searchTerm ? $repository->findByNameLike($searchTerm) : $repository->findAll();

        // Apply state filter if specified
        if ($stateFilter !== 'all') {
            $systems = array_filter($systems, function ($system) use ($stateFilter) {
                return $system->getState()?->value === $stateFilter;
            });
        }

        return $systems; // Return the filtered systems
    }

    /**
     * @author Léonard Lardeux
     * @brief Handles the addition of a new acquisition system.
     * @param Request $request HTTP request object containing form data.
     * @param EntityManagerInterface $manager Entity manager for database operations.
     * @return Response Renders the add system page view.
     */
    #[Route('/acquisitionsystem/add', name: 'app_acquisition_system_add')]
    public function add(Request $request, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Ensure the user has appropriate permissions

        // Create a form for adding a new acquisition system
        $form = $this->createForm(AcquisitionSystemType::class, new AcquisitionSystem());
        $form->handleRequest($request); // Process the form submission

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Save the new acquisition system
                $this->saveNewAcquisitionSystem($form->getData(), $manager);
                return $this->redirectToRoute('app_acquisition_system'); // Redirect to the management page
            } catch (UniqueConstraintViolationException $e) {
                $error = "Ce nom est déjà utilisé(e). Veuillez en choisir un(e) autre."; // Handle unique constraint violation
            }
        }

        // Render the add system page with the form
        return $this->render('acquisition_system/asAdd.html.twig', [
            'form' => $form->createView(),
            'error' => $error ?? null,
        ]);
    }

    /**
     * @author Léonard Lardeux
     * @brief Saves a new acquisition system to the database.
     * @param AcquisitionSystem $system The acquisition system to save.
     * @param EntityManagerInterface $manager Entity manager for database operations.
     */
    private function saveNewAcquisitionSystem(AcquisitionSystem $system, EntityManagerInterface $manager): void
    {
        $system->setState(AcquisitionSystemState::DEFAULTER); // Set the default state
        $manager->persist($system); // Persist the system entity
        $manager->flush(); // Save changes to the database
    }

    /**
     * @author Léonard Lardeux
     * @brief Handles the modification of an existing acquisition system.
     * @param string $name The name of the system to modify.
     * @param Request $request HTTP request object containing form data.
     * @param AcquisitionSystemRepository $repository Repository to fetch acquisition systems.
     * @param EntityManagerInterface $manager Entity manager for database operations.
     * @return Response Renders the modify system page view.
     */
    #[Route('/acquisitionsystem/{name}/modify', name: 'app_acquisition_system_modify')]
    public function modify(string $name, Request $request, AcquisitionSystemRepository $repository, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $system = $repository->findByName($name); // Fetch the system by name

        if (!$system) {
            throw $this->createNotFoundException('Système d\'acquisition non trouvé'); // Throw an error if not found
        }

        // Create and handle the form for modifying the system
        $form = $this->createForm(AcquisitionSystemType::class, $system);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manager->flush(); // Save changes to the database
                return $this->redirectToRoute('app_acquisition_system'); // Redirect to the management page
            } catch (UniqueConstraintViolationException $e) {
                $error = "Ce nom est déjà utilisé(e) par un autre système."; // Handle unique constraint violation
            }
        }

        // Render the modify system page with the form
        return $this->render('acquisition_system/asModify.html.twig', [
            'form' => $form->createView(),
            'error' => $error ?? null,
        ]);
    }

    /**
     * @author Léonard Lardeux
     * @brief Handles the deletion of an acquisition system.
     * @param string $name The name of the system to delete.
     * @param EntityManagerInterface $manager Entity manager for database operations.
     * @return Response Redirects to the acquisition system management page.
     */
    #[Route('/acquisitionsystem/{name}/delete', name: 'app_acquisition_system_delete', methods: ['POST'])]
    public function delete(string $name, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $system = $manager->getRepository(AcquisitionSystem::class)->findOneBy(['name' => $name]); // Fetch the system by name

        if (!$system) {
            throw $this->createNotFoundException('Système d\'acquisition non trouvé'); // Throw an error if not found
        }

        $manager->remove($system); // Remove the system entity
        $manager->flush(); // Save changes to the database

        $this->addFlash('success', 'Système d\'acquisition supprimé avec succès.'); // Add a success message

        return $this->redirectToRoute('app_acquisition_system'); // Redirect to the management page
    }
}

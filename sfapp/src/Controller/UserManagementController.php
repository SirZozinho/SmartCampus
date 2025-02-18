<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserPasswordType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserManagementController extends AbstractController
{
    /**
     * @brief Show user management
     * @param UserRepository $userRepository The users container
     * @param Request $request The request sended by query
     * @return Response Return the view
     */
    #[Route('/technicians', name: 'app_user_management')]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $searchTerm = $request->query->get('name'); // Retrieves the search term from the URL

        if ($searchTerm) {
            // If a search term is provided, use the findByName method to search for an SA by name
            $users = $userRepository->findByLoginLike($searchTerm);
        } else {
            // If no search term, retrieves all systems
            $users = $userRepository->findBy([], ['login' => 'ASC']);
        }

        return $this->render('user_management/index.html.twig', [
            'controller_name' => 'UserManagementController',
            'users' => $users,
            'searchTerm' => $searchTerm,
        ]);
    }


    /**
     * @brief Remove a target technician
     * @param EntityManagerInterface $entityManager The entity manager
     * @param UserRepository $userRepository The users container
     * @param int $id The target technician (represented by its ID)
     * @param Request $request The request sent to remove the target user
     * @return Response Redirects to users management interface
     * @author Louis PAQUEREAU
     */
    #[Route('/technicians/remove/{id}', name: 'app_user_remove')]
    public function remove(EntityManagerInterface $entityManager, UserRepository $userRepository, int $id, Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Search user by ID
        $user = $userRepository->find($id);

        if ($user) {
            // Delete user
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');

            } else {
                $this->addFlash('error', 'L\'utilisateur ne peut âs être supprimé.');
            }

        } else {
            $this->addFlash('error', 'Utilisateur introuvable.');
        }

        return $this->redirectToRoute('app_user_management');
    }

    /**
     * @brief Interface to register a new user
     * @param Request $request the request to add that new user
     * @param UserPasswordHasherInterface $userPasswordHasher The password hasher
     * @param EntityManagerInterface $entityManager The enetity manager
     * @return Response return the view
     * @author Louis PAQUEREAU
     */
    #[Route('/technicians/add', name: 'app_user_add')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $sent = false;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $user->setEmail($user->getLogin() . "@" . $user->getLogin() . ".com");

                $entityManager->persist($user);
                $entityManager->flush();
                $sent = true;
                return $this->redirectToRoute('app_user_management');
            } catch (UniqueConstraintViolationException $e) {
                // Logs an error message if a uniqueness constraint is violated
                $error = "L'utilisateur existe déjà";
            }
        }


        // Render the view with the list of rooms and the form
        return $this->render('user_management/userAdd.html.twig', [
            'text' => $sent,
            'registrationForm' => $form->createView(), // Utilise createView() pour envoyer la vue du formulaire
            'error' => $error,
        ]);
    }

    /**
     * @brief Interface to modify the password (Only for the connected user)
     * @param UserRepository $userRepository The users container
     * @param UserPasswordHasherInterface $userPasswordHasher The password hasher
     * @param Request $request The request sent by form to modify user password
     * @param EntityManagerInterface $entityManager The entity manager
     * @return Response returns the view for connected user
     * @author Louis PAQUEREAU
     */
    #[Route('/password/modify', name: 'app_user_password_edit')]
    public function userModifyPassword(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher,Request $request, EntityManagerInterface $entityManager): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $loggedUser = $this->getUser();


        $form = $this->createForm(UserPasswordType::class, $loggedUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            // encode the plain password
            $loggedUser->setPassword($userPasswordHasher->hashPassword($loggedUser, $plainPassword));

            $entityManager->persist($loggedUser);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_task_management');
        }

        return $this->render('user_management/userPasswordModify.html.twig', [
                'userPasswordForm' => $form->createView(),
                'user' => $loggedUser,
            ]);
    }
}

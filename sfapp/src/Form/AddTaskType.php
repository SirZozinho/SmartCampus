<?php

namespace App\Form;

use App\Entity\AcquisitionSystem;
use App\Entity\Room;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriorityState;
use App\Enum\TaskState;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;

class AddTaskType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Construction du formulaire
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de la tache *',
                'attr' => [
                    'style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;',
                ],
                'required' => true,
            ])
            ->add('room', EntityType::class, [
                'label' => 'Salle *',
                'class' => Room::class,
                'choice_label' => 'name',
                'query_builder' => function () {
                    // Récupérer les salles déjà attribuées à une tâche
                    $assignedRooms = $this->entityManager->getRepository(Task::class)
                        ->createQueryBuilder('t')
                        ->select('IDENTITY(t.room)')
                        ->getQuery()
                        ->getResult();

                    // Retourner les salles qui ne sont pas assignées
                    return $this->entityManager->getRepository(Room::class)
                        ->createQueryBuilder('r')
                        ->where('r.id NOT IN (:assignedRooms)')
                        ->setParameter('assignedRooms', $assignedRooms);
                },
            ])
            ->add('acquisitionSystem', EntityType::class, [
                'label' => "Système d'acquisition *",
                'class' => AcquisitionSystem::class,
                'choice_label' => 'name',
                'query_builder' => function () {
                    // Récupérer les salles déjà attribuées à une tâche
                    $assignedSA = $this->entityManager->getRepository(Task::class)
                        ->createQueryBuilder('t')
                        ->select('IDENTITY(t.acquisitionSystem)')
                        ->getQuery()
                        ->getResult();

                    // Retourner les salles qui ne sont pas assignées
                    return $this->entityManager->getRepository(AcquisitionSystem::class)
                        ->createQueryBuilder('r')
                        ->where('r.id NOT IN (:assignedSA)')
                        ->setParameter('assignedSA', $assignedSA);
                }
            ])

            ->add('user', EntityType::class, [
                'label' => 'Utilisateur',
                'class' => User::class,
                'choice_label' => 'login',
                'placeholder' => 'Aucun technicien',
                'required' => false,
            ])
            ->add('advancement', ChoiceType::class, [
                'label' => 'Avancement *',
                'attr' => [
                    'style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;',
                ],
                'choices' => [
                    'NON-ASSOCIÉ' => TaskState::NOT_ASSOCIATED,
                    'À TRAITER' => TaskState::TO_TREAT,
                    'EN COURS' => TaskState::DOING,
                    'TERMINÉ' => TaskState::COMPLETED,
                ],
            ])

            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité *',
                'attr' => [
                    'style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;',
                ],
                'choices' => [
                    'HAUTE' => TaskPriorityState::HIGH,
                    'MOYENNE' => TaskPriorityState::MEDIUM,
                    'BASSE' => TaskPriorityState::LOW,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
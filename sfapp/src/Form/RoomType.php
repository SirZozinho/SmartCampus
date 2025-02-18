<?php

namespace App\Form;

use App\Entity\Room;
use App\Enum\RoomState;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class RoomType extends AbstractType
{

    /**
     * @brief Creates form to add a new room
     * @param FormBuilderInterface $builder Form builder interface
     * @param array $options Form options
     * @return void
     * @author Enzo BIGUET
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la salle *',
                'attr' => ['style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],
                'required' => true,
            ])
            ->add('floor', IntegerType::class, [
                'label' => 'Étage *',
                'attr' => ['style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],
                'required' => true,
            ]);

        // Gestion des choix pour le champ "state"
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $room = $event->getData();
            $form = $event->getForm();

            // Si l'objet room est nouveau (pas encore persisté), on affiche seulement "Disponible" et "Pas disponible"
            $choices = [
                'DISPONIBLE' => RoomState::AVAILABLE,
                'INDISPONIBLE' => RoomState::UNAVAILABLE,
            ];

            $form->add('state', ChoiceType::class, [
                'label' => 'État *',
                'attr' => ['style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],
                'choices' => $choices,
            ]);
        });
    }


    /**
     * @brief Configures form option
     * @param OptionsResolver $resolver
     * @return void
     * @author Enzo BIGUET
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}

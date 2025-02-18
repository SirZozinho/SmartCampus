<?php

namespace App\Form;

use App\Entity\AcquisitionSystem;
use App\Entity\Room;
use App\Repository\AcquisitionSystemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomSAAssociationType extends AbstractType
{

    /**
     * @brief Creates form to create association between a room and an acquisition system
     * @param FormBuilderInterface $builder The form builder interface
     * @param array $options Form options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acquisitionSystem', EntityType::class, [
                'class' => AcquisitionSystem::class,
                'choice_label' => 'name',
                'label' => "Système d'acquisition à associer *",
                'attr' => ['style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],
                //Do a DQL request to get the list of acquisition system
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.room', 'r')
                        ->where('r.id IS NULL');
                },
            ]);
    }

    /**
     * @brief configures form options
     * @param OptionsResolver $resolver
     * @return void
     * @author Louis PAQUEREAU
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}

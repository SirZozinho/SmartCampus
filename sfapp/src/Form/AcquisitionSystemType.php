<?php

namespace App\Form;

use App\Entity\AcquisitionSystem;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class AcquisitionSystemType extends AbstractType
{
    /**
     * @brief Creates form to register a new acquisition system
     * @param FormBuilderInterface $builder Form interface builder
     * @param array $options Form options
     * @return void
     * @author Leonard LARDEUX
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom *',
                'label_html' => true,
                'attr' => ['style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],
                'required' => true,
            ])
        ;
    }

    /**
     * @brief Configures form options
     * @param OptionsResolver $resolver
     * @return void
     * @author Leonard LARDEUX
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AcquisitionSystem::class,
        ]);
    }
}

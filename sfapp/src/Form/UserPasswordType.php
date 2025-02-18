<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPasswordType extends AbstractType
{
    /**
     * @brief Creates form to update user password
     * @param FormBuilderInterface $builder Form builder interface
     * @param array $options Form options
     * @return void
     * @author Louis PAQUEREAU
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Nouveau mot de passe*',
                'attr' => ['autocomplete' => 'new-password',
                    'style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'

                ]
            ])
        ;
    }

    /**
     * @brief configure form options
     * @param OptionsResolver $resolver
     * @return void
     * @author Louis PAQUEREAU
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

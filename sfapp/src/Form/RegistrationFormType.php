<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    /**
     * @brief Creates form to register a new user
     * @param FormBuilderInterface $builder The form builder interface
     * @param array $options Form options
     * @return void
     * @author Louis PAQUEREAU
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', TextType::class, [
                'label' => 'Utilisateur *',
                'label_html' => true,
                'attr' => ['style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],

            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label_html' => true,
                'label' => 'Mot de passe *',
                'attr' => ['autocomplete' => 'new-password',
                           'style' => 'background-color: #CCCCCC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.75); margin-bottom: 20px;'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au minimum {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
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
            'data_class' => User::class,
        ]);
    }
}

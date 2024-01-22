<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Entrez votre nouveau mot de passe',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "mapped" => false,
                'required' => true,

                'constraints' => [ 
                    new Regex([
                        'pattern' => '~^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()-_+=<>?])(?!.*\s).{12}$~',
                        // au moins 1 majuscule - au moins 1 minuscule - au moins 1 chiffre - au moins un caractère special - aucun espace - au moins 12 caractères
                        'match' => true, // la valeur soumise doit correspondre entièrement à la Regex
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et avoir au moins 12 caractères.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'inputRegister'
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false, // le champs ne sera pas stocké en BDD
                'label' => "Cochez cette case pour accepter les conditions d'utilisations",
                'attr' => [
                    'class' => 'inputRegisterTerms'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions d'utilisations pour continuer",
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                "mapped" => false, // le champs ne sera pas stocké en BDD
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passes doivent être identiques.', // message d'erreur si les mdp correspondents pas
                'options' => ['attr' => ['class' => 'inputRegister']],
                'required' => true, // le mdp est obligatoire
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation du mot de passe'],

                // Regex du mot de passe
                // Regex = expression régulière: 
                // Une regex est un ensemble de règle pour vérifier si un mdp satisfait certaines conditions.
                // Cela sert à renforcer la sécurité des mdp et prévenir de l'attaque par dictionnaire

                'constraints' => [ 
                    new Regex([
                        'pattern' => 
                        '~^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()-_+=<>?])(?!.*\s).{12}$~',
                        // au moins 1 majuscule - au moins 1 minuscule - au moins 1 chiffre - 
                        // au moins un caractère special - aucun espace - au moins 12 caractères
                        'match' => true, // la valeur soumise doit correspondre entièrement à la Regex
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule,
                         un chiffre et avoir au moins 12 caractères.',
                    ]),
                ],
            ])
            
            // Honeypot: c'est une technique de sécurité utilisée pour attirer les robots (bots) en ajoutant un champs de formulaire 
            // invisible pour les utilisateurs mais qui sera visible par les bots. Donc si ce champs est rempli lors de la soumission du formulaire,
            // il est très probable que cela vienne d'un bots, on pourra alors stopper la validation du formulaire

            ->add('honeypot', HiddenType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'honeypot',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

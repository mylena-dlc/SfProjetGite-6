<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                "required" => true,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                "required" => true
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                "required" => true
            ])
            ->add('cp', IntegerType::class, [
                'label' => 'Code Postal',
                "required" => true
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                "required" => true
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                "required" => true
            ])
            ->add('phone', IntegerType::class, [
                'label' => 'Numéro de téléphone',
                "required" => true
            ])
            ->add('isMajor', CheckboxType::class, [
                'label' => 'Je confirme être majeur.',
                "required" => true,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez être majeur pour réserver un séjour.",
                    ]),
                ],
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Méthode de paiement',
                'choices' => [
                    'Carte bancaire (Stripe)' => 'stripe',
                    'PayPal' => 'paypal',
                ],
                'expanded' => true, // pour afficher comme des radios
                'multiple' => false, // pour permettre la sélection d'une seule option
                "required" => true
            ])
            ->add('payer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn submit'
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}

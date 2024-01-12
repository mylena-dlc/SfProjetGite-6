<?php

namespace App\Form;

use App\Entity\Gite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class GiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('cp', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Nombre de personne maximum',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'inputDescription'
                ],
                "required" => true
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix de la nuitée en ',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('cleaningCharge', MoneyType::class, [
                'label' => 'Prix du forfait ménage en ',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('Ajouter', SubmitType::class, [
                'attr' => [
                    'class' => 'btn submit'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gite::class,
        ]);
    }
}

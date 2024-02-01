<?php

namespace App\Form;

use App\Entity\Activity;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'activité',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'inputRegister textarea'
                ],
                "required" => true
            ])
            ->add('cp',TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'attr' => [
                    'class' => 'inputRegister'
                ],
                "required" => true
            ])
            ->add('picture', FileType::class, [
                'label' => 'Image',
                'required' => true,
                'attr' => [
                    'class' => 'inputRegister pictureInput'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '4M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
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
            'data_class' => Activity::class,
        ]);
    }
}

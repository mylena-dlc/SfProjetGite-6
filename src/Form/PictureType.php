<?php

namespace App\Form;

use App\Entity\Gite;
use App\Entity\Picture;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('picture', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'inputRegister pictureInput'
                ],

                'constraints' => [
                    new File([
                        'maxSize' => '400k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'class' => 'inputRegister'
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name', // Remplacez 'name' par la propriété que vous souhaitez afficher
                'multiple' => false, // Autorise la sélection d'une seule catégorie
                'expanded' => true, // Affiche les catégories comme des cases à cocher
                'attr' => [
                    'class' => 'inputRegister categoryInput'
                ],             
                'required' => false, // Rend le champ non obligatoire
                'placeholder' => 'Sans catégorie', // Définit la valeur du placeholder


            ])
      
            ->add('url', HiddenType::class, [ // Champ "url" de type HiddenType
                'mapped' => false, // Ne pas mapper ce champ à l'entité
            ])

            ->add('valider', SubmitType::class, [
                'attr' => [
                    'class' => 'btn submit'
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}

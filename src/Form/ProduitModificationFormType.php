<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Validator\Constraints\File;

class ProduitModificationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'form-control center input-nouveau']
            ])
            ->add('prix', MoneyType::class, [
                'currency' => 'CAD',
                'divisor' => 100,
                'label' => false,
                'attr' => [
                    'class' => 'form-control center input-nouveau',
                    'type' => 'number'
                ],
                'html5' => true
            ])
            ->add('quantiteEnStock', IntegerType::class, [
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'form-control center input-nouveau']
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'form-control center input-nouveau']
            ])
            ->add('categorie', EntityType::class, [
                'required' => true,
                'label' => false,
                'choice_label' => 'categorie',
                'class' => Categorie::class,
                'attr' => ['class' => 'form-control center input-nouveau']
            ])
            ->add(
                'imagePath',
                FileType::class,
                [
                    'data_class' => null,
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'form-control center input-nouveau'
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'image/png',
                                'image/jpeg'
                            ],
                            'mimeTypesMessage' => 'Téléverser une image valide (png, jpeg).'
                        ])
                    ]
                ]
            )
            ->add('ajout', SubmitType::class, [
                'label' => "Modifier le produit",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

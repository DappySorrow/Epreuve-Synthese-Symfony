<?php

namespace App\Form;

use App\Form\CategorieFormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class CategorieCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categories', CollectionType::class, [
                'entry_type' => CategorieFormType::class,
                'allow_add' => true,
                'label' => false,
                'required' => true
            ])
            ->add('btnAdd', ButtonType::class, [
                'label' => 'Ajouter une catégorie',
                'row_attr' => [
                    'class' => 'form-button'
                ],
                'attr' => [
                    'class' => 'addCategorie btn btn-primary',
                    'data-collection-holder-class' => 'categories'
                ]
            ])
            ->add('btnSave', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr' => [
                    'class' => 'form-button'
                ],
                'attr' => [
                    'class' => 'btn btn-primary',
                    'style' => 'margin-top:10px'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'item_collection_form'
        ]);
    }
}

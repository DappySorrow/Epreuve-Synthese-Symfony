<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SelectCommandeDetailsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etat', ChoiceType::class, [
                'required' => true,
                'label' => false,
                'choices' => [
                    'En préparation' => 'En préparation',
                    'Envoyée' => 'Envoyée',
                    'En transit' => 'En transit',
                    'Livrée' => 'Livrée'
                ],
                'attr' => [
                    'class' => 'col-6 form-select center',
                    'onchange' => 'selectCommandeDetails()',
                    'style' => 'width: 450px',
                ]
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'attr' => ['id' => 'formulaire']
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('courriel', EmailType::class, [ 
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center']])

            ->add('nom', TextType::class, [
                'required' => true, 
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center']])

            ->add('prenom', TextType::class, [
                'required' => true, 
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center']])
            
            ->add('motDePasse', RepeatedType::class, [
               'type' => PasswordType::class,
               'invalid_message' => "Les mots de passe doivent être identiques",
               'options' => ['attr' => ['class' => 'password-field form-control input-inscription center']],
               'required' => true,
               'first_options' => ['label' => false], 
               'second_options' => ['label' => false]])

            ->add('province', ChoiceType::class, [
                'required' => true,
                'label' => false,
                'choices' => [
                    'Alberta' => 'AB',
                    'Colombie‑Britannique' => 'BC',
                    'Île‑du-Prince‑Édouard' => 'PE',
                    'Manitoba' => 'MB',
                    'Nouveau‑Brunswick' => 'NB',
                    'Nouvelle‑Écosse' => 'NS',
                    'Nunavut' => 'NU',
                    'Ontario' => 'ON',
                    'Québec' => 'QC',
                    'Saskatchewan' => 'SK',
                    'Terre‑Neuve‑et‑Labrador' => 'NL',
                    'Yukon' => 'YT',
                    'Territoires du Nord‑Ouest' => 'NT'],

                'attr' => ['class' => 'form-control input-inscription center']])

            ->add('adresse', TextType::class, [
                'required' => true, 
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center']])
            

            ->add('ville', TextType::class, [
                'required' => true, 
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center']])


            ->add('codePostal', TextType::class, [
                'required' => true, 
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center codePostal']])


            ->add('telephone', TextType::class, [
                'required' => true, 
                'label' => false,
                'attr' => ['class' => 'form-control input-inscription center telephone']])


            ->add('create', SubmitType::class, [
                'label' => "Créer votre compte",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn btn-primary btn-size']
            ]);

            //-----------------------------------------------------------------------------------
            
            $builder->get('telephone')->addModelTransformer(new CallbackTransformer(
                //De la BD
                function($telephoneFromDatabase) {
                    $newTelephone = substr_replace($telephoneFromDatabase, "-", 3, 0);
                    return substr_replace($newTelephone, "-", 7, 0);
                }, 
                //De la vue
                function ($telephoneFromView) {
                    return str_replace("-", "", $telephoneFromView);
                }
            ));

            $builder->get('codePostal')->addModelTransformer(new CallbackTransformer(
                //De la BD
                function($codePostalFromDatabase) {
                    return $codePostalFromDatabase;
                }, 
                //De la vue
                function ($codePostalFromView) {
                    return str_replace(" ", "", $codePostalFromView);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}

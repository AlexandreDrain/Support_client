<?php

namespace App\Form;

use App\Form\CustomerFormType;
use App\Form\UserRegisterFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerWithUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('customer', CustomerFormType::class, [
            'label' => false
        ])
        ->add('user', UserRegisterFormType::class, [
            'label' => false
        ])
        // On enleve les champs password et roles de l'utilisateur 
        // (il sera généré via le controller)
        ->get('user')
            ->remove('password')
            ->remove('roles')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 
            'validation_groups' => 'register'
        ]);
    }
}
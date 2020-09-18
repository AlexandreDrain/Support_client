<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserRegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $roles = [
            'ROLE_ADMIN'            => 'ROLE_ADMIN',
            'ROLE_USER'             => 'ROLE_USER',
        ];

        if($options['type_register'] == 'sub_account') {
            $roles = [
                'ROLE_USER'             => 'ROLE_USER',
                'ROLE_CUSTOMER'         => 'ROLE_CUSTOMER',
                'ROLE_CUSTOMER_ADMIN'   => 'ROLE_CUSTOMER_ADMIN',
            ];
        }

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre Email'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas !',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Votre mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('roles', ChoiceType::class, [
                'multiple' => true, 
                'expanded' => true, 
                'choices' => $roles,
            ])
            ->add('service')
        ;

        if($options['type_register'] == 'sub_account') {
            $builder->remove('service');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 
            'data_class'    => User::class,
            'type_register' => 'account'
        ]);
    }
}
<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class TicketFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $statut = [

            'A valider'            => 'A valider',
            'Validé'             => 'Validé',
            'Terminé'             => 'Terminé'
        ];

        $priority = [

            "Nulle" => "0",
            "Pas urgent" => "1",
            "A voir rapidement" => "2",
            "Urgent !" => "3",

        ];

        if (isset($options['modify_ticket']) && $options['modify_ticket'] == 'new') {
            
            $builder
            ->add('titre')
            ->add('description')
            ->add('fileName', FileType::class, [
                'label' => 'Joindre un fichier ?',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            "image/*",
                            "text/plain"
                        ],
                        'mimeTypesMessage' => 'S\'il vous plaît veuillez uploader un ficher PDF valide',
                    ])
                ],
            ])
            ;

        } elseif (isset($options['modify_ticket']) && $options['modify_ticket'] == 'modify') {

            $builder
            ->add('services')
            ->add('statut', ChoiceType::class, [
                'multiple' => false, 
                'expanded' => true, 
                'choices' => $statut,
            ])
            ->add('priority', ChoiceType::class, [
                'multiple' => false, 
                'expanded' => true, 
                'choices' => $priority,
            ])
            ;

        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
            'modify_ticket' => 'new'
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Validator\StrongPassword; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('telephone', TelType::class, [
                        'attr' => [
                            'class' => ' w-100 p-1',
                            'pattern' => '^(\+33)?\s?[0-9]{1,14}$',
                        ],
                        'label' => 'Numéro de téléphone ',
                        'label_attr' => [
                            'id' => 'telephone',
                            'for' => 'telephone',
                        ],
                        'required' => false
        ])
        ->add('plainPassword', RepeatedType::class, [
                        'type'          => PasswordType::class,
                        'first_options' => [
                            'attr'          => ['class'     => 'w-100 p-1',
                            'type'        => 'password'],
                            'label'     => 'Nouveau mot de passe',
                            
                        ],
                        'second_options'=> [
                            'attr'      => ['class' => 'w-100 p-1', 
                            'type'        => 'password'],
                            'label'     => 'Confirmer le mot de passe',
                        ],
                        'required' => false,
                        'mapped' => false,
                        'constraints' => 
                        [
                            new Assert\NotCompromisedPassword(),
                            new StrongPassword(),
                        ],
                    ])

        ->add('action', HiddenType::class, [
            'mapped' => false,
            'attr' => ['id' => 'compte_action']
        ])
        ->add('submit', SubmitType::class, [
            'label'     => 'Modifier mon mot de passe', 
            'attr'      => ['class' => 'btn btn-outline-dark', 'onclick' => 'document.getElementById("compte_action").value = "changePassword";']
        ])
        ->add('modifyInformations', SubmitType::class, [
            'label'     => 'Mettre à jour les coordonnées', 
            'attr'      => ['class' => 'btn btn-outline-dark', 'onclick' => 'document.getElementById("compte_action").value = "changePhoneNumber";']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]); 
    }
}
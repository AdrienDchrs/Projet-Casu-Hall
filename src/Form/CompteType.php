<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\{AbstractType,FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{TelType,SubmitType,DateType,EmailType};

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'p-1 w-auto border-0 shadow-none',
                ],
                'label' => 'Date de naissance ',
                'label_attr' => [
                    'id' => 'bornDate',
                    'for' => 'bornDate',
                ],
                'required' => false
            ])
        ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'p-1 w-auto border-0 shadow-none',
                ],
                'label' => 'Adresse e-mail',
                'label_attr' => [
                    'id' => 'email',
                    'for' => 'email',
                ],
                'required' => false
        ])
        ->add('submit', SubmitType::class, [
            'label'     => 'Mettre à jour les coordonnées', 
            'attr'      => ['class' => 'btn btn-outline-dark']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]); 
    }
}
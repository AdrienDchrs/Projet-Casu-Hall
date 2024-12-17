<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Validator\StrongPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\{AbstractType,FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{TelType,TextType,DateType,EmailType,ChoiceType,SubmitType,RepeatedType,PasswordType};

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('civilite', ChoiceType::class, [
                'choices' => [
                    'Madame' => 0,
                    'Monsieur' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Civilité : *',
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ],
            ])
            ->add('prenomUtilisateur', TextType::class, [
                'attr'          => [
                    'minlength'     => '3', 
                    'maxlength'     => '50',
                    'placeholder'   => 'Jean', 
                    'class'         => 'p-2 border rounded mb-2'
                    ],
                'label' => 'Prénom : *',
                'label_attr' => [
                    'class' => 'fw-bold',
                    'id'    => 'prenom',
                    'for'   => 'prenom',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 3, 'max' => 50]),
                    new Assert\NotBlank(),
                ]
            ])
            ->add('nomUtilisateur', TextType::class, [
                'attr'          => [
                    'minlength'     => '2', 
                    'maxlength'     => '50',
                    'placeholder'   => 'Dupont',
                    'class'         => 'p-2 border rounded mb-2'
                    ],
                'label' => 'Nom : *',
                'label_attr' => [
                    'class' => 'fw-bold',
                    'id'    => 'nom',
                    'for'   => 'nom',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 2, 'max' => 50]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('dateNaissance', DateType::class, [
                'html5' => false,
                'label' => 'Date de naissance : *',
                'widget' => 'choice', 
                'format' => 'yyyy-MM-dd',
                'years' => range(date('Y'), 1900),
                'label_attr' => [
                    'class' => 'fw-bold',
                    'id'    => 'dateNaissance',
                    'for'   => 'dateNaissance',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range([
                        'min' => new \DateTimeImmutable('1900-01-01'),
                        'max' => new \DateTimeImmutable('now')
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr'          => [
                    'minlength'     => '6', 
                    'maxlength'     => '100',
                    'placeholder'   => 'exemple@gmail.com', 
                    'class'         => 'p-2 border rounded mb-2'
                    ],
                'label' => 'Adresse mail : *',
                'label_attr' => [
                    'class' => 'fw-bold mt-2',
                    'id'    => 'mail',
                    'for'   => 'mail',
                ],
                'constraints'   => [
                    new Assert\Length(['min'=> 6, 'max' => 100]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'          => PasswordType::class,
                'first_options' => [
                    'attr'          => [
                        'placeholder' => '********',
                        'class'       => 'p-2 border rounded mb-2',
                        'type'        => 'password'
                    ],
                    'label'     => 'Mot de passe : *', 
                    'label_attr'    => ['class' => 'fw-bold mt-2'],
                ],
                'second_options'=> [
                    'attr'          => [
                        'class'       => 'p-2 border rounded mb-2',
                        'placeholder' => '********',
                        'id'          => 'password_second',
                        'type'        => 'password'
                    ],
                    'label'     => 'Confirmer le mot de passe : * ',
                    'label_attr'    => ['class' => 'fw-bold mt-2'],
                ], 
                'constraints'   => [
                    new Assert\Length(['min' => 8, 'max' => 255]),
                    new Assert\NotBlank(),
                    new StrongPassword()
                ], 
            ])

            ->add('submit', SubmitType::class, [
                'label'     => 'S\'inscrire', 
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

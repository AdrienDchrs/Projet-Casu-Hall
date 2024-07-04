<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class InscriptionType extends AbstractType
{
    private $dateTimeImmutableTransformer;

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
                    new Assert\Length(['min' => 1, 'max' => 1]),
                    new Assert\NotBlank(),
                    new Assert\NotNull(), 
                    new Assert\Choice(['choices' => [0, 1]])
                ],
            ])
            ->add('prenomUtilisateur', TextType::class, [
                'attr'          => [
                    'minlength'     => '3', 
                    'maxlength'     => '50',
                    'placeholder'   => 'Valentino', 
                    'class'         => 'form-control'
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
                    new Assert\NotNull()
                ]
            ])
            ->add('nomUtilisateur', TextType::class, [
                'attr'          => [
                    'minlength'     => '3', 
                    'maxlength'     => '50',
                    'placeholder'   => 'Cher',
                    'class'         => 'form-control'
                    ],
                'label' => 'Nom : *',
                'label_attr' => [
                    'class' => 'fw-bold',
                    'id'    => 'nom',
                    'for'   => 'nom',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 3, 'max' => 50]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
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
                    new Assert\NotNull(), 
                    new Assert\Range([
                        'min' => new \DateTimeImmutable('1900-01-01'),
                        'max' => new \DateTimeImmutable('now'),
                        'maxMessage' => 'Vous devez être né après 1900',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr'          => [
                    'minlength'     => '10', 
                    'maxlength'     => '100',
                    'placeholder'   => 'exemple@gmail.com', 
                    'class'         => 'form-control'
                    ],
                'label' => 'Adresse e-mail  : *',
                'label_attr' => [
                    'class' => 'fw-bold mt-2',
                    'id'    => 'mail',
                    'for'   => 'mail',
                ],
                'constraints'   => [
                    new Assert\Length(['min'=> 10, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('telephone', TelType::class, [
                'attr' => [
                    'maxlength' => '17',
                    'class' => 'form-control',
                    'value' => '+33',
                    'placeholder' => '+33 6 12 34 56 78',
                ],
                'label' => 'Numéro de téléphone : ',
                'label_attr' => [
                    'class' => 'fw-bold mt-2',
                    'id' => 'telephone',
                    'for' => 'telephone',
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 17]),
                ]
            ])
            
            ->add('plainPassword', RepeatedType::class, [
                'type'          => PasswordType::class,
                'first_options' => [
                    'attr'          => [
                        'placeholder' => '********',
                        'class'       => 'form-control',
                        'type'        => 'password'
                    ],
                    'label'     => 'Mot de passe : *', 
                    'label_attr'    => ['class' => 'fw-bold mt-2'],
                ],
                'second_options'=> [
                    'attr'          => [
                        'class'       => 'form-control',
                        'placeholder' => '********',
                        'id'          => 'password_second',
                        'class'       => 'form-control',
                        'type'        => 'password'
                    ],
                    'label'     => 'Confirmer le mot de passe : * ',
                    'label_attr'    => ['class' => 'fw-bold mt-2'],
                ], 
                'constraints'   => [
                    new Assert\Length(['min' => 8, 'max' => 255]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
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

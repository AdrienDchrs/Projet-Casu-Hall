<?php

namespace App\Form;

use App\Validator\StrongPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\{AbstractType,FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{SubmitType,PasswordType,RepeatedType};

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
                        'first_options' => [
                            'attr'          => [
                                'class'     => 'p-1 w-auto mt-2',
                                'type'        => 'password',
                                'placeholder'   => '********'
                            ],

                            'label'     => 'Nouveau mot de passe : *',
                            'label_attr' => [
                                'class' => 'fw-bold mt-2',
                                'id'    => '_password',
                                'for'   => '_password',
                            ],
                        ],

                        'second_options'=> [
                            'attr'          => [
                                'class'     => 'p-1 w-auto mt-2',
                                'type'        => 'password',
                                'placeholder'   => '********'
                            ],

                            'label'     => 'Confirmer le nouveau mot de passe : *',
                            'label_attr' => [
                                'class' => 'fw-bold mt-2',
                                'id'    => '_password',
                                'for'   => '_password',
                            ],
                        ], 
                        'constraints'   => [
                            new Assert\Length(['min' => 8]),
                            new Assert\NotBlank(),
                            new Assert\NotNull(),
                            new StrongPassword()
                        ], 
                    ])

                    ->add('submit', SubmitType::class, [
                        'label'     => 'Modifier mon mot de passe', 
                        'attr'      => ['class' => 'btn btn-outline-dark']
                    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
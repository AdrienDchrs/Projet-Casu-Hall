<?php

namespace App\Form;

use App\Validator\StrongPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type'          => PasswordType::class,
                            'first_options' => [
                                'attr'          => [
                                    'class'     => 'form-control mt-2',
                                    'type'        => 'password',
                                    'placeholder'   => '********'
                                ],

                                'label'     => 'Nouveau mot de passe : *',
                                'label_attr' => [
                                    'class' => 'fw-bold mt-2',
                                    'id'    => 'password',
                                    'for'   => 'password',
                                ],
                            ],

                            'second_options'=> [
                                'attr'          => [
                                    'class'     => 'form-control mt-2',
                                    'type'        => 'password',
                                    'placeholder'   => '********'
                                ],

                                'label'     => 'Confirmer le nouveau mot de passe : *',
                                'label_attr' => [
                                    'class' => 'fw-bold mt-2',
                                    'id'    => 'password',
                                    'for'   => 'password',
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

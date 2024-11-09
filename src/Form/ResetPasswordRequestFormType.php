<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\{AbstractType,FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{EmailType,SubmitType};

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'autocomplete' => 'email',
                    'minlength'     => '10', 
                    'maxlength'     => '100',
                    'placeholder'   => 'exemple@gmail.com', 
                    'class'         => 'form-control mt-2'
                ],
                
                'label' => 'Adresse e-mail  : *',
                'label_attr' => [
                    'class' => 'fw-bold mt-2',
                    'id'    => 'mail',
                    'for'   => 'mail',
                ],
                'constraints' => [
                    new Assert\Length(['min'=> 10, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ],
            ]) 
            ->add('submit', SubmitType::class, [
                'label'     => 'Réinitialiser mon mot de passe', 
                'attr'      => ['class' => 'btn btn-outline-dark mt-2']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

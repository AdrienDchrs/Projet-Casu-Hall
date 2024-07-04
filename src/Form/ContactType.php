<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emailContact', EmailType::class, [
                'attr'          => [
                    'minlength'     => '10', 
                    'maxlength'     => '100',
                    'placeholder'   => 'exemple@gmail.com',
                    'for'           => 'mail',
                    'id'            => 'mail',
                    'class'         => 'p-2 border rounded mb-2'
                    ],
                'label' => 'Adresse e-mail  : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id'    => 'mail',
                    'for'   => 'mail',
                ],
                'constraints'   => [
                    new Assert\Length(['min'=> 10, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('objet', TextType::class, [
                'attr'          => [
                    'minlength'     => '1', 
                    'maxlength'     => '100', 
                    'id'            => 'objet',
                    'for'           => 'objet',
                    'class'         => 'p-2 border rounded mb-2'
                    ],
                'label' => 'Objet : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id'    => 'objet',
                    'for'   => 'objet',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 1, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'attr'          => [
                    'minlength'     => '1',
                    'rows'          => '8',
                    'cols'          => '40',
                    'id'            => 'commentaire',
                    'for'           => 'commentaire',
                    'class'         => 'p-2 border rounded mb-2'
                    ],
                'label' => 'Commentaire : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id'    => 'commentaire',
                    'for'   => 'commentaire',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 1]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label'     => 'Envoyer le mail', 
                'attr'      => ['class' => 'btn btn-outline-dark']
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'mapped' => false,
                'constraints' => new Recaptcha3(['message' => 'Captcha invalide, veuillez réessayer.']),
                'action_name' => 'contact',
                'attr' => [
                    'id' => 'recaptcha3',
                    'class' => 'd-none'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}

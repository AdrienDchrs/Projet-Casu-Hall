<?php

namespace App\Form;

use App\Entity\Marque;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\{AbstractType,FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{SubmitType,HiddenType,TextType};

class MarqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('action', HiddenType::class, [
                'mapped' => false,
                'attr' => ['id' => 'marque_action']
            ])    
            ->add('nomMarque', TextType::class, [
                'attr' => [
                    'minlength' => '4', 
                    'maxlength' => '50',
                    'id' => 'nom',
                    'class' => 'p-2 border rounded mb-2'
                ],
                'label' => 'form.nom_marque.label',
                'translation_domain' => 'messages',
                'label_attr' => [
                    'class' => 'fw-bold mb-2',
                ],
                'constraints' => [
                    new Assert\Length(['min' => 4, 'max' => 50]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])            
            ->add('imageFile', VichImageType::class, [
                'attr' => [
                    'for' => 'image',
                    'id' => 'image',
                    'class' => 'form-control-file mb-4',
                    'placeholder' => 'form.choisir_fichier',
                ],
                'label' => 'form.logo_marque.label',
                'translation_domain' => 'messages',
                'label_attr' => [
                    'class' => 'fw-bold mb-2',
                ],
                'download_uri' => false,
                'image_uri' => false,
                'allow_delete' => false,
                'required' => true,
            ])            
            ->add('addBrand', SubmitType::class, [
                'label'     => 'form.ajouter_marque.bouton', 
                'translation_domain' => 'messages',
                'attr'      => ['class' => 'btn btn-outline-success', 'onclick' => 'document.getElementById("marque_action").value = "add";']
            ])

            ->add('deleteBrand', SubmitType::class, [
                'label'     => 'form.supprimer_marque.bouton', 
                'translation_domain' => 'messages',
                'attr'      => ['class' => 'btn btn-outline-danger', 'onclick' => 'document.getElementById("marque_action").value = "delete";']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Marque::class,
        ]);
    }
}

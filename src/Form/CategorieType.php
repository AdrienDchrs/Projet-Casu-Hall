<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\{AbstractType,FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{TextType,SubmitType,HiddenType};

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nomCategorie', TextType::class, [
            'attr'          => [
                'minlength'     => '4', 
                'maxlength'     => '50',
                'for'           => 'nom',
                'id'            => 'nom',
                'class'         => 'p-2 border rounded mb-5'
                ],
            'label' => 'Nom de la catégorie : *',
            'label_attr' => [
                'class' => 'fw-bold mb-2 mt-3',
                'id'    => 'nom',
                'for'   => 'nom',
            ],
            'constraints'   => [
                new Assert\Length(['min'=> 4, 'max' => 50]),
                new Assert\NotBlank(),
                new Assert\NotNull()
            ]
        ])
        ->add('action', HiddenType::class, [
            'mapped' => false,
            'attr' => ['id' => 'categorie_action']
        ])
        ->add('addCategorie', SubmitType::class, [
            'label'     => 'Ajouter la catégorie', 
            'attr'      => ['class' => 'btn btn-outline-success', 'onclick' => 'document.getElementById("categorie_action").value = "add";']
        ])

        ->add('deleteCategorie', SubmitType::class, [
            'label'     => 'Supprimer la catégorie', 
            'attr'      => ['class' => 'btn btn-outline-danger', 'onclick' => 'document.getElementById("categorie_action").value = "delete";']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}

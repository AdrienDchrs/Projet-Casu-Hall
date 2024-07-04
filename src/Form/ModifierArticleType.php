<?php

namespace App\Form;

use App\Entity\Marque;
use App\Entity\Article;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ModifierArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomArticle', TextType::class, [
                'attr' => [
                    'minlength' => '4', 
                    'maxlength' => '50',
                    'for' => 'nom',
                    'id' => 'nom',
                    'class' => 'p-2 border rounded mb-2'
                ],
                'label' => 'Nom de l\'article : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'nom',
                    'for' => 'nom',
                ],
                'constraints'   => [
                    new Assert\Length(['min'=> 4, 'max' => 50]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('prix', NumberType::class, [
                'attr' => [
                    'minlength' => '1', 
                    'maxlength' => '10',
                    'for' => 'prix',
                    'id' => 'prix',
                    'class' => 'form-control',
                    'min' => '0',
                    'step' => '0.10'
                ],
                'label' => 'Prix de l\'article : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'prix',
                    'for' => 'prix',
                ],
                'constraints'   => [
                    new Assert\Length(['min'=> 0]),
                    new Assert\PositiveOrZero(),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('taille', ChoiceType::class, [
                'attr' => [
                    'for' => 'taille',
                    'id' => 'taille',
                    'class' => 'form-control'
                ],
                'label' => 'Taille : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'taille',
                    'for' => 'taille',
                ],
                'choices' => [
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL',
                    'XXL' => 'XXL',
                    'XXXL' => 'XXXL'
                ],
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
                
            ])
            ->add('quantite_stock', IntegerType::class, [
                'attr' => [
                    'minlength' => '0',
                    'for' => 'quantite_stock',
                    'id' => 'quantite_stock',
                    'class' => 'form-control',
                    'min' => '0'
                ],
                'label' => 'Quantité en stock : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'quantite_stock',
                    'for' => 'quantite_stock',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 0]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows'      => '8',
                    'cols'      => '40',
                    'for' => 'description',
                    'id' => 'description',
                    'class' => 'form-control'
                ],
                'label' => 'Description de l\'article : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'description',
                    'for' => 'description',
                ],
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('note', IntegerType::class, [
                'attr' => [
                    'for' => 'note',
                    'id' => 'note',
                    'class' => 'form-control',
                    'min' => '1',
                    'max' => '10'
                ],
                'label' => 'Note de l\'article : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'note',
                    'for' => 'note',
                ],
                'constraints'   => [
                    new Assert\Length(['min' => 1, 'max' => 10]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('idCategorie', ChoiceType::class, [
                'attr' => [
                    'for' => 'cateogorie',
                    'id' => 'cateogorie',
                    'class' => 'form-control'
                ],
                'label' => 'Catégorie : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'cateogorie',
                    'for' => 'cateogorie',
                ],
                'choices' => $options['categories'],
                'choice_label' => function (?Categorie $categorie) {
                    return $categorie->getNomCategorie();
                },
                'choice_value' => function (?Categorie $categorie) {
                    return $categorie ? $categorie->getIdCategorie() : '';
                },
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('idMarque', ChoiceType::class, [
                'attr' => [
                    'for' => 'marque',
                    'id' => 'marque',
                    'class' => 'form-control'
                ],
                'label' => 'Marque : *',
                'label_attr' => [
                    'class' => 'fw-bold mb-1',
                    'id' => 'marque',
                    'for' => 'marque',
                ],
                'choices' => $options['marques'],
                'choice_label' => function (Marque $marque) {
                    return $marque->getNomMarque();
                },
                'choice_value' => function (?Marque $marque) {
                    return $marque ? $marque->getIdMarque() : '';
                },
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ]
            ])
            ->add('modifierArticle', SubmitType::class, [
                'label' => 'Modifier l\'article', 
                'attr' => ['class' => 'btn btn-outline-dark']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'marques' => [],
            'categories' => []
        ]);
    }
}

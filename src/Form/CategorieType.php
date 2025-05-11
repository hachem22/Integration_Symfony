<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank; // <-- Ajoutez cette ligne
use Symfony\Component\Validator\Constraints as Assert;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom de la catégorie',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom de la catégorie...'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom de la catégorie ne peut pas être vide.']),
                new Assert\Length([
                    'min' => 5,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                ]),
            ],
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la description...', 'rows' => 4],
            'constraints' => [
                new Assert\NotBlank(['message' => 'La description ne peut pas être vide.']),
                new Assert\Length([
                    'min' => 10,
                    'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                ]),
            ],
        ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}

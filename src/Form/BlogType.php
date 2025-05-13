<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Title cannot be empty']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Title cannot exceed 255 characters']),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Content cannot be empty']),
                    new Assert\Length(['min' => 20, 'minMessage' => 'Content must be at least 20 characters']),
                ],
            ])
            ->add('author', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'username', // Assurez-vous que 'username' est un champ valide dans Utilisateur
                'label' => 'Author',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Select an author',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Author cannot be empty']),
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Technology' => 'technology',
                    'Health' => 'health',
                    'Lifestyle' => 'lifestyle',
                    // Ajoutez plus de catégories si nécessaire
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Category cannot be empty']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('Nom', TextType::class, [
            'label' => 'Nom de la formation',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom de la formation'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom de la formation ne peut pas être vide.',  // Message d'erreur en français pour champ vide
                ]),
                new Length([
                    'min' => 4,
                    'max' => 20,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-ZÀ-ÿ\s]+$/u',
                    'message' => 'Le nom doit contenir uniquement des lettres et des espaces.',
                ]),
            ],
        ])
        
        ->add('Description', TextType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Ajoutez une description'],
            'constraints' => [
                new NotBlank([
                    'message' => 'La description ne peut pas être vide.',
                ]),
                new Length([
                    'min' => 4,
                    'max' => 20,
                    'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-ZÀ-ÿ\s]+$/u',
                    'message' => 'La description ne doit contenir que des lettres et des espaces.',
                ]),
            ],
        ])
            ->add('Type', ChoiceType::class, [
                'label' => 'Type de formation',
                'choices' => [
                    'En ligne' => 'en_ligne',
                    'Présentiel' => 'presentiel',
                ],
                'placeholder' => 'Sélectionnez un type', // Optional placeholder
                'expanded' => false, // false = dropdown, true = radio buttons
                'multiple' => false, // Single selection
                'attr' => ['class' => 'form-control'], // Add Bootstrap styling
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type de formation est obligatoire.',
                    ]),
                ],
            ])
            ->add('Date', DateTimeType::class, [
                'label' => 'Date de la formation',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date est obligatoire.',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être aujourd\'hui ou plus tard.',
                    ]),
                ],
            ]);
            
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}

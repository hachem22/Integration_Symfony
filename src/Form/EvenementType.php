<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\Evenement;
use App\Entity\Formation;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThan;
class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('Nom', TextType::class, [
            'label' => 'Nom de l\'événement',
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom de l\'événement est obligatoire.',
                ]),
                new Length([
                    'min' => 4,
                    'max' => 70,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-ZÀ-ÿ\s\'’\-]+$/u',
                    'message' => 'Le nom ne peut contenir que des lettres et des espaces.',
                ]),
            ],
        ])
            /*->add('Description', TextType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank()
                ]
            ])*/
            ->add('Description', TextType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description est obligatoire.',
                    ]),
                    new Length([
                        'min' => 4,
                        'max' => 1000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\'’\-]+$/u',
                        
                        
    'message' => 'La description ne peut contenir que des lettres et des espaces.',
                    ]),
                ],
            ])
            
            /*->add('Capacite', IntegerType::class, [
                'label' => 'Capacité',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                    new LessThanOrEqual(20)
                ]
            ])*/
            ->add('Capacite', IntegerType::class, [
                'label' => 'Capacité',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La capacité est obligatoire.',
                    ]),
                    new GreaterThan([
                        'value' => 10,
                        'message' => 'La capacité doit être supérieure à 10 et ne peut pas être négative.',
                    ]),
                    new LessThanOrEqual([
                        'value' => 70,
                        'message' => 'La capacité ne peut pas dépasser 70.',
                    ]),
                ],
            ])
            ->add('formations', EntityType::class, [
                'class' => Formation::class,   // The entity to fetch choices from
                'choice_label' => 'Nom',      // The property displayed in the dropdown
                'placeholder' => 'Sélectionnez une formation', // Optional placeholder
                'multiple' => false,          // Single selection
                'expanded' => false,          // Dropdown (true for radio buttons)
                'required' => false,          // Not mandatory
                'constraints' => [
        new NotBlank([
            'message' => 'Il faut choisir une formation',    ]),
        ],
            ])
            ->add('lieu', ChoiceType::class, [
                'label' => 'Lieu de l\'événement',
                'placeholder' => 'Sélectionnez un gouvernorat',
                'choices' => [
                    'Ariana' => 'Ariana',
                    'Beja' => 'Beja',
                    'Ben Arous' => 'Ben Arous',
                    'Bizerte' => 'Bizerte',
                    'Gabes' => 'Gabes',
                    'Gafsa' => 'Gafsa',
                    'Jendouba' => 'Jendouba',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Kebili' => 'Kebili',
                    'Kef' => 'Kef',
                    'Mahdia' => 'Mahdia',
                    'Manouba' => 'Manouba',
                    'Medenine' => 'Medenine',
                    'Monastir' => 'Monastir',
                    'Nabeul' => 'Nabeul',
                    'Sfax' => 'Sfax',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Siliana' => 'Siliana',
                    'Sousse' => 'Sousse',
                    'Tataouine' => 'Tataouine',
                    'Tozeur' => 'Tozeur',
                    'Tunis' => 'Tunis',
                    'Zaghouan' => 'Zaghouan',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le lieu de l\'événement est obligatoire.',
                    ]),
                ],
            ])
           ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,  // The entity linked to the form
        ]);
    }
}

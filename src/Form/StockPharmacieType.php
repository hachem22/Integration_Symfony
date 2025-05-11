<?php

namespace App\Form;
// Import nécessaire
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use App\Entity\StockPharmacie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\File;

class StockPharmacieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Entrez le nom du produit']
            ])
            ->add('type', ChoiceType::class, [
                'choices' => StockPharmacie::getTypeChoices(),
                'label' => 'Type de produit',
                'placeholder' => 'Sélectionnez un type',
                'required' => true
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix'
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité'
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Détails',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Entrez les détails du produit (optionnel)'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire'])
                ],
                'attr' => [
                    'rows' => 4,
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la description du produit'
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG)',
                    ])
                ],
            ])
            ->add('statu', ChoiceType::class, [
                'choices' => [
                    'En stock' => true,
                    'Rupture' => false
                ],
                'label' => 'Statut'
            ])
            ->add('dateExpiration', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date d\'expiration',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockPharmacie::class,
        ]);
    }
}

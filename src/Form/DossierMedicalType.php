<?php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('groupeSanguin', TextType::class, [
                'label' => 'Groupe Sanguin',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: A+',
                ],
                'required' => true,
            ])
            ->add('allergies', TextareaType::class, [
                'label' => 'Allergies',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Allergie aux noix, pollen...',
                    'rows' => 3,
                ],
            ])
            ->add('vaccinations', TextareaType::class, [
                'label' => 'Vaccinations',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Vaccin contre la grippe, Covid-19...',
                    'rows' => 3,
                ],
            ])
            ->add('antecedentsMedicaux', TextareaType::class, [
                'label' => 'Antécédents Médicaux',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Diabète, hypertension...',
                    'rows' => 3,
                ],
                'required' => false,
            ])
            ->add('traitementsEnCours', TextareaType::class, [
                'label' => 'Traitements en Cours',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Médicaments, thérapies...',
                    'rows' => 3,
                ],
                'required' => false,
            ])
            ->add('remarques', TextareaType::class, [
                'label' => 'Remarques',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Informations supplémentaires...',
                    'rows' => 3,
                ],
                'required' => false,
            ])
            ->add('patient', EntityType::class, [
                'class' => Utilisateur::class, // Entité Utilisateur
                'choice_label' => 'nom', // Afficher le nom du patient
                'label' => 'Patient',
                'attr' => [
                    'class' => 'form-control',
                ],
                'placeholder' => 'Sélectionnez un patient',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMedical::class,
        ]);
    }
}

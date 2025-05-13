<?php

// RendezVousType.php
namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\Service;
use App\Entity\Utilisateur;
use App\Enum\UtilisateurRole; // Import the enum
use Doctrine\ORM\EntityRepository; // Import EntityRepository
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        ->add('service', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'nom',
            'choice_value' => 'id',
            'placeholder' => 'Sélectionnez un service',
            'attr' => ['class' => 'form-select'],
        ])
            ->add('medecin', EntityType::class, [
                'class' => Utilisateur::class,
                // Add query_builder to initially load only doctors
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.utilisateurRole = :role')
                        ->setParameter('role', UtilisateurRole::Medecin) // Use the enum
                        ->orderBy('u.Nom', 'ASC');
                },
                'placeholder' => 'Sélectionnez un service d\'abord',
                'attr' => ['class' => 'select-medecin'],
                'choice_value' => 'id',
                // Improve choice label
                'choice_label' => function(Utilisateur $medecin) {
                    return $medecin->getPrenom() . ' ' . $medecin->getNom();
                },
                'invalid_message' => 'Le médecin sélectionné est invalide.',
                'required' => true,
                // 'disabled' => true, // Temporarily removed - dropdown will appear enabled but filtering won't work until 403 is fixed
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => true,
                'attr' => ['class' => 'form-control select-date'], // Changed class for consistency
            ])
           ;
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
            'services' => [],
        ]);
    }
    

 
}
<?php

namespace App\Form;

use App\Entity\Planning;
use App\Entity\RendezVous;
use App\Entity\Service;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RendezVousModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $services = $options['services']; // Liste des services passée en paramètre

        $builder
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous'
            ])
            ->add('heure', TextType::class, [ // Ajoutez ce champ
                'label' => 'Heure du rendez-vous',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'HH:MM',
                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$', // Validation de l'heure
                ],
            ])
            ->add('service', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'nom',
            'choice_value' => 'id',
            'placeholder' => 'Sélectionnez un service',
            'attr' => ['class' => 'form-select'],
        ])
            ->add('medecin', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'placeholder' => 'Sélectionnez un service d\'abord',
                'attr' => ['class' => 'select-medecin'],
                'required' => true,
            ]);
        }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
            'services' => [], // Liste des services à passer en option

        ]);
    }
}

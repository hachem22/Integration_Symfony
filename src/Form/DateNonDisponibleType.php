<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateNonDisponibleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('medecin', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un médecin',
                'label' => 'Médecin',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('dateNonDisponible', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Non Disponible',
                'attr' => ['class' => 'form-control', 'type' => 'date'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}

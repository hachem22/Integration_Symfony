<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Service;
use Masterminds\HTML5\Entities;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ChambreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $services = $options['services'];

        $builder
            ->add('num', TextType::class, [
                'label' => 'Numéro de la chambre',
            ])
            
            ->add('type', TextType::class, [
                'label' => 'Type de chambre',
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
            ])
            ->add('capacite', IntegerType::class, [
                'label' => 'Capacité',
            ])
            ->add('active', ChoiceType::class, [
                'label' => 'État de la chambre',
                'choices' => [
                    'Disponible' => 'disponible',
                    'Occupée' => 'occupee',
                    'Maintenance' => 'maintenance',
                ],
                'placeholder' => 'Sélectionnez un état',
            ])
            ->add('position', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'placeholder' => 'Sélectionnez un service',
                'attr' => ['class' => 'form-select'],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chambre::class,
            'services' => [],

        ]);
    }
}

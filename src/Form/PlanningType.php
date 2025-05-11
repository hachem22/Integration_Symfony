<?php

// src/Form/PlanningType.php

namespace App\Form;

use App\Entity\Planning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDisponible', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de disponibilité',
            ])
            ->add('tempsReserver', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure de disponibilité',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
}



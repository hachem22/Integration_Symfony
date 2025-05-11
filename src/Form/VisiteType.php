<?php

namespace App\Form;

use App\Entity\Visite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateVisite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la Visite',
                'attr' => ['class' => 'form-control'],
                'required' => true
            ])
            ->add('diagnostic', TextareaType::class, [
                'label' => 'Diagnostic',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'diagnostic-field',
                    'rows' => 4,
                ],
            ])
            ->add('ordonnance', TextareaType::class, [
                'label' => 'Ordonnance',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'ordonnance-field',
                    'rows' => 4,
                ],
            ])
            ->add('certificat', TextareaType::class, [
                'label' => 'Certificat',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visite::class,
        ]);
    }
}
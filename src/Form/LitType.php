<?php

namespace App\Form;

use App\Entity\Lit;
use App\Entity\Chambre;
use App\Enum\LitStatut; // Assurez-vous que cette énumération existe
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('num')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Libre' => 'libre',
                    'Occupé' => 'occupe',
                ],
            ])
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => 'num',
                'placeholder' => 'Sélectionnez une chambre',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lit::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\TraitementReclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\ReclamationStatut;
use Symfony\Component\Validator\Constraints as Assert;


class TraitementReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le commentaire est obligatoire.']),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'Le commentaire doit contenir au moins {{ limit }} caractères.'
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ajoutez un commentaire...']
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État du traitement',
                'choices' => array_merge(
                    ['Sélectionnez un état' => null],
                    array_combine(
                        array_map(fn($etat) => $etat->value, ReclamationStatut::cases()),
                        ReclamationStatut::cases()
                    )
                ),
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotNull(['message' => 'Veuillez sélectionner un état.']),
                ],
            ])
            ->add('dateTraitement', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de traitement',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'), // Empêche les dates passées
                ],
                'constraints' => [
                    new Assert\NotNull(['message' => 'Veuillez sélectionner une date de traitement.']),
                ],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TraitementReclamation::class,
        ]);
    }
}

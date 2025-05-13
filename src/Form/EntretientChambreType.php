<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\EntretientChambre;
use App\Enum\StatutEntretientChambre;
use App\Enum\TypeEntretient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use App\Entity\Utilisateur;
use App\Enum\UtilisateurRole;
use Doctrine\ORM\EntityRepository;

class EntretientChambreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($type) => $type->value, TypeEntretient::cases()),
                    TypeEntretient::cases()
                ),
                'choice_label' => fn($choice) => $choice->value,
            ])
            ->add('datedebut', DateTimeType::class, [
                'widget' => 'single_text',
               
                'label' => 'Date de début',
            ])
            ->add('datefin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($statut) => $statut->value, StatutEntretientChambre::cases()),
                    StatutEntretientChambre::cases()
                ),
                'choice_label' => fn($choice) => $choice->value,
            ])
            ->add('details', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description (sera complétée automatiquement si laissée vide)'
                ]
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
   
                if ($data && $data->getType() && empty($data->getDetails())) {
                    $data->setDetails($data->autoCompleteDescription());
                }
            })
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => 'num',
                'label' => 'Chambre associée',
                'placeholder' => 'Sélectionnez une chambre',
            ]);
           
           
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntretientChambre::class,
        ]);
    }
}
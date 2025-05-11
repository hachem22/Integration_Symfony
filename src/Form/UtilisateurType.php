<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Entity\Service;
use App\Enum\UtilisateurRole;
use App\Enum\MedecinSpecialite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Nom', TextType::class)
            ->add('Prenom', TextType::class)
            ->add('Email', TextType::class)
            ->add('Adress', TextType::class)
            ->add('Tel', TextType::class)
            ->add('utilisateurRole', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(
                        fn($role) => $role->value,
                        array_filter(UtilisateurRole::cases(), fn($role) => !in_array($role->value, ['Administrateur', 'Patient']))
                    ),
                    array_filter(UtilisateurRole::cases(), fn($role) => !in_array($role->value, ['Administrateur', 'Patient']))
                ),
                'choice_label' => fn($choice) => $choice->value,
                'choice_value' => fn(?UtilisateurRole $role) => $role ? $role->value : null,
                'mapped' => true,
                'attr' => [
                    'class' => 'js-role-selector' // JavaScript will listen to this
                ]
            ])

            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'Nom', // Adjust based on the actual property name in Service entity
                'placeholder' => 'Select a service',
                'required' => false, // Make it optional and controlled by JavaScript
                'attr' => ['class' => 'js-service-field'] // JavaScript control
            ])
            ->add('Grade', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'medecin-field']
            ])
            ->add('medecinSpecilaite', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($specialite) => $specialite->value, MedecinSpecialite::cases()),
                    MedecinSpecialite::cases()
                ),
                'choice_label' => fn($choice) => $choice->value,
                'choice_value' => fn(?MedecinSpecialite $specialite) => $specialite ? $specialite->value : null,
                'placeholder' => 'Choisir une spécialité',
                'required' => false,
                'attr' => ['class' => 'medecin-field']
            ])
            ->add('image', FileType::class, [
                'label' => 'Profile Image',
                'required' => false, // The image is optional
                'mapped' => false, // This field is not mapped to the entity
            ]);

        // Handle dynamic form update when the role is changed
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            // Hide service field unless the role is Medecin, Infirmier, or Responsable
            if (!isset($data['utilisateurRole']) || !in_array($data['utilisateurRole'], ['Medecin', 'Infirmier', 'Responsable'])) {
                $data['service'] = null;
                $event->setData($data);
            }

            // Hide Grade and MedecinSpecialite unless the role is "Medecin"
            if (!isset($data['utilisateurRole']) || $data['utilisateurRole'] !== 'Medecin') {
                unset($data['Grade'], $data['medecinSpecilaite']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}

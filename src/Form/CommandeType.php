<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\StockPharmacie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stockPharmacie', EntityType::class, [
                'class' => StockPharmacie::class,
                'choice_label' => 'nom',
                'label' => 'Produit',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Positive()
                ],
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false
            ])
            ->add('dateLivraison', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de livraison doit être ultérieure à aujourd\'hui'
                    ])
                ],
                'data' => new \DateTimeImmutable('+3 days'),
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => Commande::STATUT_EN_ATTENTE,
                    'Livrée' => Commande::STATUT_LIVREE,
                    'Annulée' => Commande::STATUT_ANNULEE
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}

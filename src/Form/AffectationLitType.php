<?php
namespace App\Form;

use App\Entity\Lit;
use App\Entity\Utilisateur;
use App\Enum\UtilisateurRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AffectationLitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner un patient',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.utilisateurRole = :role')
                        ->setParameter('role', UtilisateurRole::Patient);
                },
            ]);
          
        }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lit::class,
        ]);
    }
}

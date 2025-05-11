<?php

namespace App\Repository;

use App\Entity\TraitementReclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TraitementReclamation>
 */
class TraitementReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TraitementReclamation::class);
    }
    public function findByReclamation($reclamationId): array
{
    return $this->createQueryBuilder('t')
        ->andWhere('t.reclamation = :reclamationId')
        ->setParameter('reclamationId', $reclamationId)
        ->orderBy('t.dateTraitement', 'DESC') // Trier par date (optionnel)
        ->getQuery()
        ->getResult();
}
public function searchTraitementsParEtat(?string $etat)
{
    $qb = $this->createQueryBuilder('t');

    if (!empty($etat)) {
        $qb->andWhere('t.etat = :etat')
           ->setParameter('etat', $etat);
    }

    return $qb->getQuery()->getResult();
}
public function countTraitementsByRating(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.rating, COUNT(t.id) as count')
            ->andWhere('t.rating IS NOT NULL') // Ne récupère que les traitements avec un rating
            ->groupBy('t.rating')
            ->orderBy('t.rating', 'ASC')
            ->getQuery()
            ->getResult();
    }




    //    /**
    //     * @return TraitementReclamation[] Returns an array of TraitementReclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TraitementReclamation
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

<?php

namespace App\Repository;

use App\Entity\EntretientChambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\StatutEntretientChambre;

/**
 * @extends ServiceEntityRepository<EntretientChambre>
 */
class EntretientChambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntretientChambre::class);
    }

    //    /**
    //     * @return EntretientChambre[] Returns an array of EntretientChambre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EntretientChambre
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    
    public function findExpiredEntretients(\DateTimeInterface $currentDateTime)
{
    return $this->createQueryBuilder('e')
        ->where('e.datefin < :currentDateTime')  // Cela compare à la fois la date et l'heure
        ->andWhere('e.statut != :statut')
        ->setParameter('currentDateTime', $currentDateTime)
        ->setParameter('statut', StatutEntretientChambre::termine)
        ->getQuery()
        ->getResult();
}
}

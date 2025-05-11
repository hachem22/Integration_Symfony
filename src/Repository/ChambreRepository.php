<?php

namespace App\Repository;

use App\Entity\Chambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chambre>
 */
class ChambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chambre::class);
    }

    //    /**
    //     * @return Chambre[] Returns an array of Chambre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Chambre
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findBySearchTerm(string $searchTerm): array
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.position', 'p')
        ->where('c.num LIKE :searchTerm')
        ->orWhere('p.Nom LIKE :searchTerm')
        ->orWhere('c.active LIKE :searchTerm')
        ->setParameter('searchTerm', '%' . $searchTerm . '%')
        ->getQuery()
        ->getResult();
}
// Dans ChambreRepository.php
public function findByFilters(array $filters)
{
    $qb = $this->createQueryBuilder('c')
               ->leftJoin('c.position', 's')
               ->addSelect('s');
    
    // Filtre par type (simple, double, suite)
    if (!empty($filters['type'])) {
        $qb->andWhere('c.type = :type')
           ->setParameter('type', $filters['type']);
    }
    
    // Filtre par statut (disponible, occupee, maintenance)
    if (!empty($filters['active'])) {
        $qb->andWhere('c.active = :active')
           ->setParameter('active', $filters['active']);
    }
    
    // Filtre par capacité (min et max)
    if (!empty($filters['capaciteMin'])) {
        $qb->andWhere('c.capacite >= :capaciteMin')
           ->setParameter('capaciteMin', $filters['capaciteMin']);
    }
    
    if (!empty($filters['capaciteMax'])) {
        $qb->andWhere('c.capacite <= :capaciteMax')
           ->setParameter('capaciteMax', $filters['capaciteMax']);
    }
    
    // Filtre par service/position
    if (!empty($filters['service'])) {
        $qb->andWhere('s.id = :serviceId')
           ->setParameter('serviceId', $filters['service']);
    }
    
    // Recherche par numéro de chambre
    if (!empty($filters['search'])) {
        $qb->andWhere('c.num LIKE :search OR c.localisation LIKE :search')
           ->setParameter('search', '%' . $filters['search'] . '%');
    }
    
    return $qb->orderBy('c.num', 'ASC')
              ->getQuery()
              ->getResult();
}
public function findBySearchCriteriaSorted(
    ?string $searchTerm = null, 
    ?string $sortBy = 'num', 
    ?string $sortDirection = 'ASC'
) {
    $queryBuilder = $this->createQueryBuilder('c');

    // Recherche
    if ($searchTerm) {
        $queryBuilder
            ->andWhere('c.num LIKE :searchTerm  OR c.active LIKE :searchTerm')
            ->setParameter('searchTerm', "%{$searchTerm}%");
    }

    // Validation des paramètres de tri
    $allowedSortColumns = ['num', 'type',  'active', 'capacite'];
    $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'num';
    $sortDirection = in_array(strtoupper($sortDirection), ['ASC', 'DESC']) ? $sortDirection : 'ASC';

    $queryBuilder->orderBy("c.{$sortBy}", $sortDirection);

    return $queryBuilder->getQuery()->getResult();
}
    
}

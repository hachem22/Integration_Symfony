<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Utilisateur;


/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }
    
    public function searchReclamations(?string $statut, ?string $type, ?int $categorieId, $utilisateur = null)
    {
        $qb = $this->createQueryBuilder('r');
    
        // Filtrer par utilisateur uniquement si ce n'est pas un admin (utilisé dans index())
        if ($utilisateur) {
            $qb->andWhere('r.utilisateur = :utilisateur')
               ->setParameter('utilisateur', $utilisateur);
        }
    
        if ($statut) {
            $qb->andWhere('r.statut = :statut')
               ->setParameter('statut', $statut);
        }
    
        if ($type) {
            $qb->andWhere('r.type = :type')
               ->setParameter('type', $type);
        }
    
        if ($categorieId) {
            $qb->andWhere('r.categorie = :categorieId')
               ->setParameter('categorieId', $categorieId);
        }
    
        return $qb->getQuery()->getResult();
    }
    public function createSearchQueryBuilder(?string $statut, ?string $type, ?int $categorieId)
{
    $qb = $this->createQueryBuilder('r');

    if ($statut) {
        $qb->andWhere('r.statut = :statut')
           ->setParameter('statut', $statut);
    }

    if ($type) {
        $qb->andWhere('r.type = :type')
           ->setParameter('type', $type);
    }

    if ($categorieId) {
        $qb->andWhere('r.categorie = :categorieId')
           ->setParameter('categorieId', $categorieId);
    }

    return $qb;
}

    
public function countReclamationsToday(Utilisateur $utilisateur): int
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.utilisateur = :utilisateur')
        ->andWhere('r.datecreation >= :today')
        ->setParameter('utilisateur', $utilisateur)
        ->setParameter('today', new \DateTime('today'))
        ->getQuery()
        ->getSingleScalarResult();
}

// src/Repository/ReclamationRepository.php

public function findAllReclamations(): array
{
    return $this->createQueryBuilder('r')
        ->orderBy('r.datecreation', 'DESC') // Trier par date de création, optionnel
        ->getQuery()
        ->getResult();
}
public function countTotalReclamations(): int
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countReclamationsByStatut(string $statut): int
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.statut = :statut')
        ->setParameter('statut', $statut)
        ->getQuery()
        ->getSingleScalarResult();
}

public function countReclamationsByType(string $type): int
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.type = :type')
        ->setParameter('type', $type)
        ->getQuery()
        ->getSingleScalarResult();
}
public function searchByGlobalCriteria(?string $globalSearch): array
{
    // Créer un QueryBuilder pour l'entité Reclamation avec l'alias 'r'
    $queryBuilder = $this->createQueryBuilder('r')
        // Jointure gauche avec l'entité Categorie (alias 'c')
        ->leftJoin('r.categorie', 'c')
        // Jointure gauche avec l'entité Utilisateur (alias 'u')
        ->leftJoin('r.utilisateur', 'u');

    // Si un terme de recherche global est fourni
    if ($globalSearch) {
        // Ajouter une condition WHERE avec plusieurs critères de recherche
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                // Recherche dans la description de la réclamation
                $queryBuilder->expr()->like('r.description', ':globalSearch'),
                // Recherche dans la cible de la réclamation
                $queryBuilder->expr()->like('r.cible', ':globalSearch'),
                // Recherche dans le statut de la réclamation
                $queryBuilder->expr()->like('r.statut', ':globalSearch'),
                // Recherche dans l'email de l'utilisateur associé
                $queryBuilder->expr()->like('u.email', ':globalSearch')
            )
        )
        // Définir le paramètre de recherche avec des wildcards (%)
        ->setParameter('globalSearch', '%' . $globalSearch . '%');
    }

    // Exécuter la requête et retourner les résultats sous forme de tableau
    return $queryBuilder->getQuery()->getResult();
}
public function countReclamationsByDay(): array
{
    $sql = "
        SELECT DATE(r.datecreation) as day, COUNT(r.id) as count
        FROM reclamation r
        GROUP BY day
        ORDER BY day ASC
    ";
    $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
    $result = $stmt->executeQuery();
    return $result->fetchAllAssociative();
}

public function countReclamationsByMonth(): array
{
    $sql = "
        SELECT MONTH(r.datecreation) as month, COUNT(r.id) as count
        FROM reclamation r
        GROUP BY month
        ORDER BY month ASC
    ";
    $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
    $result = $stmt->executeQuery();
    return $result->fetchAllAssociative();
}
    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

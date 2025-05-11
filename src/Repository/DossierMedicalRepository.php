<?php

namespace App\Repository;

use App\Entity\DossierMedical;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DossierMedical>
 */
class DossierMedicalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DossierMedical::class);
    }
    public function findWithVisites(int $id): ?DossierMedical
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.visites', 'v')
            ->addSelect('v')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findByPatientId(int $patientId): ?DossierMedical
    {
        $results = $this->createQueryBuilder('d')
            ->andWhere('d.patient = :patientId')
            ->setParameter('patientId', $patientId)
            ->getQuery()
            ->getResult(); // Get all results

        return $results[0] ?? null; // Return the first one, or null if empty
    }
    public function findByFilters(?string $patient = null, ?string $groupeSanguin = null, ?string $allergie = null): array
{
    $queryBuilder = $this->createQueryBuilder('d')
        ->leftJoin('d.patient', 'p');
        
    // Filtrer par nom ou prénom du patient
    if ($patient) {
        $queryBuilder->andWhere('p.Nom LIKE :patient OR p.Prenom LIKE :patient') // Utilisez 'Nom' et 'Prenom'
            ->setParameter('patient', '%' . $patient . '%');
    }
        
    // Filtrer par groupe sanguin
    if ($groupeSanguin) {
        $queryBuilder->andWhere('d.groupeSanguin = :groupeSanguin')
            ->setParameter('groupeSanguin', $groupeSanguin);
    }
        
    // Filtrer par allergie
    if ($allergie) {
        $queryBuilder->andWhere('d.allergies LIKE :allergie')
            ->setParameter('allergie', '%' . $allergie . '%');
    }
        
    return $queryBuilder->getQuery()->getResult();
}

    
}

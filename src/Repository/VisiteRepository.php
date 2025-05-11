<?php

namespace App\Repository;

use App\Entity\Visite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visite>
 */
class VisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visite::class);
    }
    public function findByDossierMedical(int $dossierMedicalId): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.dossierMedical', 'd') // Assurez-vous de faire une jointure correcte avec DossierMedical
            ->andWhere('d.id = :dossierMedicalId')
            ->setParameter('dossierMedicalId', $dossierMedicalId)
            ->getQuery()
            ->getResult();
    }

    
}

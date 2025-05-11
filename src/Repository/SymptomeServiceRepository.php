<?php

namespace App\Repository;

use App\Entity\SymptomeService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SymptomeService>
 */
class SymptomeServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SymptomeService::class);
    }

    public function findBySymptome(string $symptome): array
    {
        // Convertir en minuscules
        $symptome = mb_strtolower($symptome, 'UTF-8');
        
        // Supprimer les accents
        $symptome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $symptome);

        return $this->createQueryBuilder('s')
            ->where('LOWER(s.symptome) LIKE :symptome')
            ->setParameter('symptome', '%' . $symptome . '%')
            ->getQuery()
            ->getResult(); // Changed to getResult to handle multiple results
    }
}
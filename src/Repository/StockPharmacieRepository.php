<?php

namespace App\Repository;

use App\Entity\StockPharmacie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockPharmacieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockPharmacie::class);
    }

    public function findByMedicamentAndType(string $medicament, string $type): ?StockPharmacie
    {
        return $this->createQueryBuilder('s')
            ->where('s.nom = :nom')
            ->andWhere('s.type = :type')
            ->setParameter('nom', $medicament)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findByPriceThreshold(float $price, string $operator = '<'): array
    {
        if (!in_array($operator, ['<', '<=', '>', '>='])) {
            throw new \InvalidArgumentException('Opérateur invalide, utilisez "<", "<=", ">" ou ">="');
        }

        return $this->createQueryBuilder('s')
            ->andWhere("s.prix $operator :price")
            ->setParameter('price', $price)
            ->orderBy('s.prix', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.actif = :actif')
            ->setParameter('actif', true)
            ->getQuery()
            ->getResult();
    }
}

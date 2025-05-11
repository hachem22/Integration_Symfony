<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findByProduct($productId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.stockPharmacie = :product')
            ->setParameter('product', $productId)
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingDeliveries(\DateTimeInterface $startDate)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.dateDelivree >= :startDate')
            ->setParameter('startDate', $startDate)
            ->orderBy('c.dateDelivree', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

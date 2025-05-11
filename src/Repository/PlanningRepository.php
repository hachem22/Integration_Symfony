<?php

// src/Repository/PlanningRepository.php
namespace App\Repository;

use App\Entity\Planning;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    /**
     * Récupère les dates disponibles pour un médecin donné.
     */
    public function findDatesDisponiblesByMedecin(Utilisateur $medecin): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les heures disponibles pour un médecin et une date donnée.
     */
    public function findHeuresDisponiblesByMedecinAndDate(Utilisateur $medecin, \DateTimeInterface $date): array
    {
        // Récupérer les plannings pour ce médecin et cette date
        $plannings = $this->createQueryBuilder('p')
            ->andWhere('p.medecin = :medecin')
            ->andWhere('p.dateDisponible = :date')
            ->setParameter('medecin', $medecin)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

        // Générer les heures disponibles
        $heuresDisponibles = [];
        foreach ($plannings as $planning) {
            if (empty($planning->getTempsReserver())) {
                // Si le temps n'est pas réservé, ajouter les intervalles de temps disponibles
                for ($hour = 8; $hour <= 17; $hour++) {
                    $heuresDisponibles[] = sprintf('%02d:00-%02d:00', $hour, $hour + 1);
                }
            }
        }

        return $heuresDisponibles;
    }
}
<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Utilisateur;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Récupère les rendez-vous en attente d'une sélection d'heure
     *
     * @return RendezVous[]
     */
    public function findRendezVousEnAttenteSelectionHeure(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.rendezVousStatus = :status')
            ->setParameter('status', 'En attente de sélection de l\'heure')
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les heures disponibles pour un jour donné
     *
     * @param \DateTimeInterface $date
     * @return array Liste des heures disponibles (format H:i)
     */
    public function findHeuresDisponibles(\DateTimeInterface $date): array
{
    // Liste des heures possibles entre 08h00 et 17h00
    $heuresPossibles = [];
    for ($i = 8; $i <= 17; $i++) {
        $heuresPossibles[] = sprintf('%02d:00', $i);
    }

    // Récupérer les heures déjà réservées pour cette date
    $qb = $this->createQueryBuilder('r')
        ->select('p.tempsReserver')
        ->join('r.planning', 'p')
        ->where('r.date = :date')
        ->setParameter('date', $date->format('Y-m-d'))
        ->getQuery()
        ->getResult();

    // Transformer le résultat en tableau simple
    $heuresReservees = array_map(fn($row) => $row['tempsReserver'], $qb);

    // Retourner uniquement les heures disponibles
    return array_diff($heuresPossibles, $heuresReservees);
}
public function findAcceptedForTomorrow(\DateTime $tomorrow)
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.date = :tomorrow')
        ->andWhere('r.rendezVousStatus = :status')
        ->setParameter('tomorrow', $tomorrow->format('Y-m-d'))
        ->setParameter('status', 'Confirme')
        ->getQuery()
        ->getResult();
}
public function findByFilters(?string $statut = null, ?string $medecinNom = null)
{
    $qb = $this->createQueryBuilder('r');
    
    if ($statut) {
        $qb->andWhere('r.rendezVousStatus = :statut')
           ->setParameter('statut', $statut);
    }
    
    if ($medecinNom) {
        $qb->leftJoin('r.medecin', 'm')
           ->andWhere('m.nom LIKE :medecinNom')
           ->setParameter('medecinNom', '%' . $medecinNom . '%');
    }

    return $qb->getQuery()->getResult();
}
/**
     * Find rendez-vous for a specific médecin within a date range
     * 
     * @param Utilisateur $medecin
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return RendezVous[]
     *  */
public function findRendezVousByMedecinAndDateRange(Utilisateur $medecin, \DateTimeInterface $start, \DateTimeInterface $end)
{
    return $this->createQueryBuilder('rv')
        ->where('rv.medecin = :medecin')
        ->andWhere('rv.date BETWEEN :start AND :end')
        ->setParameter('medecin', $medecin)
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->orderBy('rv.date', 'ASC')
        ->getQuery()
        ->getResult();
}
public function countRendezVousByType(Utilisateur $medecin, \DateTimeInterface $start, \DateTimeInterface $end, string $serviceType)
    {
        return $this->createQueryBuilder('rv')
            ->select('COUNT(rv.id)')
            ->innerJoin('rv.service', 's')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date BETWEEN :start AND :end')
            ->andWhere('s.nom = :serviceType')
            ->setParameter('medecin', $medecin)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('serviceType', $serviceType)
            ->getQuery()
            ->getSingleScalarResult();
    }
    /**
     * Find the next upcoming rendez-vous for a médecin
     * 
     * @param Utilisateur $medecin
     * @return RendezVous|null
     */
    public function findNextRendezVous(Utilisateur $medecin)
    {
        $now = new \DateTime('now');

        return $this->createQueryBuilder('rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date > :now')
            ->setParameter('medecin', $medecin)
            ->setParameter('now', $now)
            ->orderBy('rv.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
     /**
     * Count new patients for a médecin within the last 7 days
     * 
     * @param Utilisateur $medecin
     * @return int
     */
    public function countNewPatients(Utilisateur $medecin): int
    {
        $weekAgo = new \DateTime('-7 days');

        $query = $this->getEntityManager()->createQuery(
            'SELECT COUNT(DISTINCT p.id) 
             FROM App\Entity\RendezVous rv 
             JOIN rv.patient p 
             WHERE rv.medecin = :medecin 
             AND rv.date >= :weekAgo'
        )
        ->setParameter('medecin', $medecin)
        ->setParameter('weekAgo', $weekAgo);

        return $query->getSingleScalarResult();
    }
   /**
     * Count total patients for a specific medecin
     */
    public function countPatientsByMedecin(Utilisateur $medecin): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->innerJoin('u.rendezVousPris', 'rv')
            ->where('rv.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count new patients for a medecin in the last week
     */
    public function countNewPatientsByMedecinLastWeek(Utilisateur $medecin): int
    {
        $weekAgo = new \DateTime('-7 days');

        return $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->innerJoin('u.rendezVousPris', 'rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date >= :weekAgo')
            ->setParameter('medecin', $medecin)
            ->setParameter('weekAgo', $weekAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    
}

<?php

namespace App\Repository;
use App\Enum\UtilisateurRole;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findOneByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['Email' => $email]);
    }
    public function findMedecins(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.utilisateurRole = :role')
            ->setParameter('role', UtilisateurRole::Medecin)
            ->getQuery()
            ->getResult();
    }
    public function findByFaceEncoding(array $faceEncoding): ?Utilisateur
    {
        $users = $this->findAll();
        foreach ($users as $user) {
            if ($user->getFaceEncoding()) {
                $distance = $this->computeFaceDistance($user->getFaceEncoding(), $faceEncoding);
                if ($distance < 0.6) { // Adjust the threshold as needed
                    return $user;
                }
            }
        }
        return null;
    }

    private function computeFaceDistance(array $encoding1, array $encoding2): float
    {
        $distance = 0;
        for ($i = 0; $i < count($encoding1); $i++) {
            $distance += pow($encoding1[$i] - $encoding2[$i], 2);
        }
        return sqrt($distance);
    }
    public function findMedecinsByService(int $serviceId): array
    {
        return $this->createQueryBuilder('u')
    ->join('u.service', 's')
    ->andWhere('s.id = :serviceId')
    ->andWhere('u.utilisateurRole = :role')
    ->setParameter('serviceId', $serviceId)
    ->setParameter('role', UtilisateurRole::Medecin)
    ->getQuery()
    ->getResult();
    }
   
    // Ajouter cette méthode pour compatibilité au cas où elle est appelée ailleurs
    public function findByService(int $serviceId): array
    {
        return $this->findMedecinsByService($serviceId);
    }
    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

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

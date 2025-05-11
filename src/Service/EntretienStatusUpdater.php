<?php
namespace App\Service;

use App\Entity\EntretientChambre;
use App\Enum\StatutEntretientChambre;
use Doctrine\ORM\EntityManagerInterface;

class EntretienStatusUpdater
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateStatus(): void
    {
        $entretiens = $this->entityManager->getRepository(EntretientChambre::class)
            ->createQueryBuilder('e')
            ->where('e.datefin < :now')
            ->andWhere('e.statut != :statut')
            ->setParameter('now', new \DateTime())
            ->setParameter('statut', StatutEntretientChambre::termine)
            ->getQuery()
            ->getResult();

        foreach ($entretiens as $entretien) {
            $entretien->setStatut(StatutEntretientChambre::termine);
            $this->entityManager->persist($entretien);
        }

        $this->entityManager->flush();
    }
}
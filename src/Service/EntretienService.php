<?php

namespace App\Service;

use App\Entity\Lit;
use App\Entity\EntretientChambre;
use App\Enum\StatutEntretientChambre;
use App\Enum\TypeEntretient;
use Doctrine\ORM\EntityManagerInterface;

class EntretienService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Crée un entretien de chambre automatiquement lorsqu'un patient est désaffecté
     */
    public function creerEntretienApresDepartPatient(Lit $lit): void
    {
        $chambre = $lit->getChambre();
       
        if (!$chambre) {
            return; // Ne rien faire si le lit n'est pas associé à une chambre
        }

        // Vérifier si tous les lits de la chambre sont libres
        $tousLitsLibres = true;
        foreach ($chambre->getLits() as $litChambre) {
            if ($litChambre->getStatus() === 'Occupé') {
                $tousLitsLibres = false;
                break;
            }
        }

        // Si tous les lits sont libres, créer un entretien et mettre la chambre en maintenance
        if ($tousLitsLibres) {
            // Créer l'entretien
            $entretien = new EntretientChambre();
            $entretien->setChambre($chambre);
           
            // Configurer l'entretien
            $entretien->setType(TypeEntretient::NETTOYAGE);
            $entretien->setStatut(StatutEntretientChambre::en_cours);
           
            // Dates de début et fin (par exemple, commencer aujourd'hui et finir dans 1 jour)
            $dateDebut = new \DateTime();
            $dateFin = (new \DateTime())->modify('+1 day');
           
            $entretien->setDatedebut($dateDebut);
            $entretien->setDatefin($dateFin);
            $entretien->setDetails('Entretien automatique après départ patient');
           
            // Mettre la chambre en maintenance
            $chambre->setActive('Maintenance');
           
            $this->entityManager->persist($entretien);
            $this->entityManager->persist($chambre);
            $this->entityManager->flush();
        }
    }
}

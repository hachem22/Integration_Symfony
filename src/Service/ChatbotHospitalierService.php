<?php
namespace App\Service;

use App\Repository\ChambreRepository;
use App\Repository\LitRepository;
use App\Repository\EntretientChambreRepository;

class ChatbotHospitalierService
{
    private $chambreRepository;
    private $litRepository;
    private $entretientRepository;

    public function __construct(
        ChambreRepository $chambreRepository,
        LitRepository $litRepository,
        EntretientChambreRepository $entretientRepository
    ) {
        $this->chambreRepository = $chambreRepository;
        $this->litRepository = $litRepository;
        $this->entretientRepository = $entretientRepository;
    }

    public function traiterRequete(string $requete): array
    {
        // Normaliser la requête
        $requeteNormalisee = $this->normaliserRequete($requete);

        // Réponse spéciale pour les salutations en arabe
        if (in_array($requeteNormalisee, ['labes "hmdl"', 'labes', 'hmdl'])) {
            return [
                'statut' => 'succes',
                'message' => 'Labes, hmdl! Je suis là pour vous aider. Que puis-je faire pour vous aujourd\'hui ?'
            ];
        }

        // Réponse spéciale pour les salutations
        if (in_array($requeteNormalisee, ['slm', 'salm', 'slem', 'salem'])) {
            return [
                'statut' => 'succes',
                'message' => 'Aalik Slem! Comment puis-je vous aider aujourd\'hui ?'
            ];
        }

        // Vérifier si la requête est un numéro de chambre
        if (preg_match('/chambre\s*(\d+)/i', $requeteNormalisee, $matches)) {
            $numeroChambre = $matches[1];
            return $this->rechercherDetailsChambre($numeroChambre);
        }

        // Vérifier si la requête concerne l'historique des entretiens d'une chambre
        if (preg_match('/historique\s*entretiens\s*chambre\s*(\d+)/i', $requeteNormalisee, $matches)) {
            $numeroChambre = $matches[1];
            return $this->obtenirHistoriqueMaintenanceChambre($numeroChambre);
        }

        // Détecter l'intention
        $intention = $this->detecterIntention($requeteNormalisee);

        // Traiter selon l'intention
        switch ($intention) {
            case 'DISPONIBILITE_CHAMBRE':
                return $this->verifierDisponibiliteChambre($requeteNormalisee);
            case 'DISPONIBILITE_LIT':
                return $this->verifierDisponibiliteLitAvecOccupes($requeteNormalisee);
            case 'HISTORIQUE_MAINTENANCE':
                return $this->obtenirHistoriqueMaintenanceSpecifique($requeteNormalisee);
            default:
                return [
                    'statut' => 'info',
                    'message' => 'Je ne comprends pas votre demande. Pouvez-vous reformuler ?'
                ];
        }
    }

    private function rechercherDetailsChambre(string $numeroChambre): array
    {
        // Rechercher la chambre par son numéro
        $chambre = $this->chambreRepository->findOneBy(['num' => $numeroChambre]);

        if (!$chambre) {
            return [
                'statut' => 'erreur',
                'message' => "Aucune chambre trouvée avec le numéro $numeroChambre."
            ];
        }

        // Récupérer les lits de cette chambre
        $lits = $this->litRepository->findBy(['chambre' => $chambre]);
        $detailsLits = array_map(function($lit) {
            return [
                'numero' => $lit->getNum(),
                'statut' => $lit->getType(), // 'libre' ou 'occupé'
                'details' => method_exists($lit, 'getDetails') ? $lit->getDetails() : 'Aucun détail supplémentaire'
            ];
        }, $lits);

        // Récupérer l'historique de maintenance pour cette chambre
        $entretiensChambres = $this->entretientRepository->findBy(['chambre' => $chambre]);
        $detailsEntretiens = array_map(function($entretien) {
            return [
                'dateDebut' => $entretien->getDatedebut()->format('Y-m-d'),
                'dateFin' => $entretien->getDatefin()->format('Y-m-d'),
                'type' => method_exists($entretien->getType(), 'value') ? $entretien->getType()->value : $entretien->getType(),
                'statut' => method_exists($entretien->getStatut(), 'value') ? $entretien->getStatut()->value : $entretien->getStatut(),
                'description' => method_exists($entretien, 'getDescription') ? $entretien->getDescription() : 'Aucune description',
                'technicien' => method_exists($entretien, 'getTechnicien') && $entretien->getTechnicien() 
                    ? $entretien->getTechnicien()->getNom() 
                    : 'Non assigné'
            ];
        }, $entretiensChambres);

        // Détails supplémentaires de la chambre
        $detailsChambre = [
            'numero' => $chambre->getNum(),
            'type' => $chambre->getType(),
            'capacite' => $chambre->getCapacite(),
            'service' => $chambre->getPosition() ? $chambre->getPosition()->getNom() : 'Non affecté',
            'statut' => method_exists($chambre, 'getActive') ? $chambre->getActive() : 'Statut inconnu'
        ];

        return [
            'statut' => 'succes',
            'message' => "Détails de la chambre $numeroChambre",
            'chambre' => $detailsChambre,
            'lits' => $detailsLits,
            'entretiens' => $detailsEntretiens
        ];
    }

    private function obtenirHistoriqueMaintenanceChambre(string $numeroChambre): array
    {
        // Rechercher la chambre par son numéro
        $chambre = $this->chambreRepository->findOneBy(['num' => $numeroChambre]);

        if (!$chambre) {
            return [
                'statut' => 'erreur',
                'message' => "Aucune chambre trouvée avec le numéro $numeroChambre."
            ];
        }

        // Récupérer l'historique de maintenance pour cette chambre
        $entretiensChambres = $this->entretientRepository->findBy(['chambre' => $chambre]);

        if (empty($entretiensChambres)) {
            return [
                'statut' => 'info',
                'message' => "Aucun entretien trouvé pour la chambre $numeroChambre."
            ];
        }

        $detailsEntretiens = array_map(function($entretien) {
            return [
                'dateDebut' => $entretien->getDatedebut()->format('Y-m-d'),
                'dateFin' => $entretien->getDatefin()->format('Y-m-d'),
                'type' => method_exists($entretien->getType(), 'value') ? $entretien->getType()->value : $entretien->getType(),
                'statut' => method_exists($entretien->getStatut(), 'value') ? $entretien->getStatut()->value : $entretien->getStatut(),
                'description' => method_exists($entretien, 'getDescription') ? $entretien->getDescription() : 'Aucune description',
                'technicien' => method_exists($entretien, 'getTechnicien') && $entretien->getTechnicien() 
                    ? $entretien->getTechnicien()->getNom() 
                    : 'Non assigné'
            ];
        }, $entretiensChambres);

        return [
            'statut' => 'succes',
            'message' => "Historique des entretiens pour la chambre $numeroChambre",
            'entretiens' => $detailsEntretiens
        ];
    }

    private function detecterIntention(string $requete): string
    {
        // Vérifier d'abord si c'est une recherche de chambre
        if (preg_match('/chambre\s*(\d+)/i', $requete)) {
            return 'DETAILS_CHAMBRE';
        }

        // Vérifier si c'est une demande d'historique des entretiens d'une chambre
        if (preg_match('/historique\s*entretiens\s*chambre\s*(\d+)/i', $requete)) {
            return 'HISTORIQUE_MAINTENANCE_CHAMBRE';
        }

        $motsCles = [
            'DISPONIBILITE_CHAMBRE' => ['disponible', 'libre', 'chambre'],
            'DISPONIBILITE_LIT' => ['lit', 'libre', 'occupé'],
            'HISTORIQUE_MAINTENANCE' => ['maintenance', 'entretien', 'historique']
        ];

        foreach ($motsCles as $intention => $motsIntention) {
            foreach ($motsIntention as $motCle) {
                if (stripos($requete, $motCle) !== false) {
                    return $intention;
                }
            }
        }

        return 'INCONNU';
    }

    private function normaliserRequete(string $requete): string
    {
        return strtolower(trim($requete));
    }

    private function verifierDisponibiliteChambre(string $requete): array
    {
        $chambresDisponibles = $this->chambreRepository->findBy(['active' => 'disponible']);

        if (empty($chambresDisponibles)) {
            return [
                'statut' => 'info',
                'message' => 'Aucune chambre disponible pour le moment.'
            ];
        }

        $detailsChambres = array_map(function($chambre) {
            return [
                'numero' => $chambre->getNum(),
                'type' => $chambre->getType(),
                'capacite' => $chambre->getCapacite(),
                'service' => $chambre->getPosition() ? $chambre->getPosition()->getNom() : 'Non affecté'
            ];
        }, $chambresDisponibles);

        return [
            'statut' => 'succes',
            'message' => 'Il y a ' . count($detailsChambres) . ' chambres disponibles.',
            'chambres' => $detailsChambres
        ];
    }

    private function verifierDisponibiliteLitAvecOccupes(string $requete): array
    {
        // Récupérer tous les lits (libres et occupés)
        $tousLesLits = $this->litRepository->findAll();

        if (empty($tousLesLits)) {
            return [
                'statut' => 'info',
                'message' => 'Aucun lit trouvé.'
            ];
        }

        $detailsLits = array_map(function($lit) {
            return [
                'numero' => $lit->getNum(),
                'statut' => $lit->getType(), // 'libre' ou 'occupé'
                'chambre' => $lit->getChambre() ? $lit->getChambre()->getNum() : 'Sans chambre',
                'service' => $lit->getChambre() && $lit->getChambre()->getPosition() 
                    ? $lit->getChambre()->getPosition()->getNom() 
                    : 'Non affecté'
            ];
        }, $tousLesLits);

        // Séparer les lits libres et occupés
        $litsLibres = array_filter($detailsLits, function($lit) {
            return $lit['statut'] === 'libre';
        });

        $litsOccupes = array_filter($detailsLits, function($lit) {
            return $lit['statut'] === 'occupé';
        });

        return [
            'statut' => 'succes',
            'message' => 'Il y a ' . count($litsLibres) . ' lits libres et ' . count($litsOccupes) . ' lits occupés.',
            'lits' => $detailsLits
        ];
    }

    private function obtenirHistoriqueMaintenanceSpecifique(string $requete): array
    {
        // Récupérer uniquement les enregistrements de maintenance actuels
        $enregistrementsMaintenance = $this->entretientRepository->findAll();

        if (empty($enregistrementsMaintenance)) {
            return [
                'statut' => 'info',
                'message' => 'Aucun historique de maintenance trouvé.'
            ];
        }

        $historiques = array_map(function($enregistrement) {
            return [
                'chambre' => $enregistrement->getChambre()->getNum(),
                'dateDebut' => $enregistrement->getDatedebut()->format('Y-m-d'),
                'dateFin' => $enregistrement->getDatefin()->format('Y-m-d'),
                'type' => method_exists($enregistrement->getType(), 'value') 
                    ? $enregistrement->getType()->value 
                    : $enregistrement->getType(),
                'statut' => method_exists($enregistrement->getStatut(), 'value') 
                    ? $enregistrement->getStatut()->value 
                    : $enregistrement->getStatut(),
                'description' => method_exists($enregistrement, 'getDescription') 
                    ? $enregistrement->getDescription() 
                    : 'Aucune description'
            ];
        }, $enregistrementsMaintenance);

        return [
            'statut' => 'succes',
            'message' => 'Historique de maintenance (' . count($historiques) . ' enregistrements) :',
            'maintenances' => $historiques
        ];
    }
}
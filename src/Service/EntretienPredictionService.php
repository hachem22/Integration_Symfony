<?php
namespace App\Service;

use App\Entity\Chambre;
use App\Entity\EntretientChambre;
use App\Repository\EntretientChambreRepository;
use App\Repository\ChambreRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EntretienPredictionService
{
    private $entretientRepository;
    private $chambreRepository;
    private $httpClient;

    public function __construct(
        EntretientChambreRepository $entretientRepository,
        ChambreRepository $chambreRepository,
        HttpClientInterface $httpClient
    ) {
        $this->entretientRepository = $entretientRepository;
        $this->chambreRepository = $chambreRepository;
        $this->httpClient = $httpClient;
    }

    /**
     * Prédire les chambres qui nécessiteront un entretien dans les prochains jours.
     */
    public function predireChambresAEntretenir(): array
    {
        // Récupérer les données historiques des entretiens
        $historiqueEntretiens = $this->entretientRepository->findAll();

        // Préparer les données pour l'IA
        $data = [];
        foreach ($historiqueEntretiens as $entretien) {
            $data[] = [
                'chambre_id' => $entretien->getChambre()->getId(),
                'type_entretien' => $entretien->getType(),
                'duree_entretien' => $entretien->getDatefin()->diff($entretien->getDatedebut())->days,
                'statut' => $entretien->getStatut(),
            ];
        }

        // Envoyer les données à un modèle IA (ex: API Python Flask/FastAPI)
        $response = $this->httpClient->request('POST', 'http://localhost:5000/predict', [
            'json' => $data,
        ]);

        // Décoder la réponse
        $predictions = $response->toArray();

        return $predictions;
    }

    /**
     * Prédire les créneaux optimaux pour planifier les entretiens.
     */
    public function predireCreneauxOptimaux(): array
    {
        // Logique pour prédire les meilleurs créneaux (ex: utilisation d'un modèle de séries temporelles)
        return ['creneaux' => 'à implémenter'];
    }
}
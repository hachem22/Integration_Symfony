<?php
// src/Service/OrdonnanceAIService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrdonnanceAIService {
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function generateOrdonnance(string $maladie): string
    {
        try {
            // Appel à notre service Python dédié aux ordonnances
            $response = $this->httpClient->request('POST', 'http://localhost:5000/generate_prescription', [
                'json' => [
                    'maladie' => $maladie
                ],
            ]);
            
            $content = $response->toArray();
            return $content['ordonnance'] ?? $this->getLocalOrdonnance($maladie);
        } catch (\Exception $e) {
            // Fallback en cas d'erreur avec le service Python
            return $this->getLocalOrdonnance($maladie);
        }
    }
    
    // Méthode de repli en cas de problème avec l'API
    private function getLocalOrdonnance(string $maladie): string
    {
        $ordonnances = [
            'mal de tête' => "Paracétamol 1000mg, 1 comprimé toutes les 6 heures. Ne pas dépasser 4g par jour.",
            'fièvre' => "Ibuprofène 400mg, 1 comprimé toutes les 8 heures. À prendre au cours d'un repas.",
            'douleur thoracique' => "Examen médical complémentaire recommandé avant toute prescription médicamenteuse.",
            'vertiges' => "Tanganil 500mg, 1 comprimé 3 fois par jour pendant 5 jours.",
            'toux' => "Sirop antitussif, 1 cuillère à soupe 3 fois par jour après les repas.",
            'nausée' => "Motilium 10mg, 1 comprimé avant les repas principaux, maximum 3 fois par jour."
        ];
        
        return $ordonnances[$maladie] ?? "Prescription à déterminer après examen médical approfondi.";
    }
}
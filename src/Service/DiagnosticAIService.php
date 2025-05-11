<?php
// src/Service/DiagnosticAIService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiagnosticAIService {
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function generateDiagnostic(string $maladie): string {
        try {
            $response = $this->httpClient->request('POST', 'http://localhost:5000/generate_diagnostic', [
                'json' => [
                    'maladie' => $maladie
                ],
            ]);
            
            $content = $response->toArray();
            return $content['diagnostic'] ?? "Impossible de générer un diagnostic pour $maladie.";
        } catch (\Exception $e) {
            // Fallback en cas d'erreur avec le service Python
            return $this->getLocalDiagnostic($maladie);
        }
    }

    // Méthode de repli en cas de problème avec l'API
    private function getLocalDiagnostic(string $maladie): string
    {
        $diagnostics = [
            'fièvre' => "Élévation de la température corporelle suggérant une réponse immunitaire à une infection virale ou bactérienne. Évaluer les symptômes associés pour déterminer l'origine. Surveiller l'évolution et l'apparition de symptômes aggravants.",
            'douleur thoracique' => "Douleur thoracique nécessitant une évaluation urgente pour exclure pathologies cardiaques, pulmonaires ou digestives graves. Caractériser la douleur (type, irradiation, facteurs aggravants/apaisants). ECG et bilan recommandés.",
            'vertiges' => "Sensation de déséquilibre pouvant indiquer troubles vestibulaires, hypotension orthostatique, anxiété ou déshydratation. Évaluer les antécédents neurologiques et cardiovasculaires. Examen otologique et tests d'équilibre recommandés.",
            'toux' => "Symptôme respiratoire pouvant indiquer une irritation ou inflammation des voies respiratoires. Causes possibles: infection virale, asthme, RGO, allergie. Surveiller la durée, le caractère (sec/productif) et les symptômes associés.",
            'nausée' => "Symptôme digestif pouvant résulter d'une irritation gastrique, trouble de l'équilibre, réaction médicamenteuse ou anxiété. Investigation recommandée si persistance ou association à d'autres symptômes digestifs.",
            'mal de tête' => "Céphalée pouvant indiquer tension musculaire, fatigue visuelle, migraine ou céphalée de tension. Évaluer le type, la localisation et les facteurs déclenchants/aggravants. Consultation si intensité sévère ou symptômes neurologiques associés.",
        ];
        
        return $diagnostics[$maladie] ?? "Diagnostic non disponible pour $maladie. Une consultation approfondie est nécessaire.";
    }
}
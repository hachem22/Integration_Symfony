<?php

namespace App\Controller;

use App\Service\MedicalDiagnosisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ApiController extends AbstractController {
    private MedicalDiagnosisService $medicalDiagnosisService;
    private LoggerInterface $logger;
    
    public function __construct(MedicalDiagnosisService $medicalDiagnosisService, LoggerInterface $logger)
    {
        $this->medicalDiagnosisService = $medicalDiagnosisService;
        $this->logger = $logger;
    }
    
    #[Route('/api/generate-diagnostic', name: 'generate_diagnostic', methods: ['POST'])]
    public function generateDiagnostic(Request $request): JsonResponse
    {
        try {
            // Log the incoming request for debugging purposes
            $content = $request->getContent();
            $this->logger->info('API generate_diagnostic called');
            $this->logger->debug('Request content: ' . $content);
    
            // Decode the JSON payload and check for errors
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('JSON decoding error: ' . json_last_error_msg());
                return new JsonResponse([
                    'error' => 'Requête invalide. JSON mal formaté: ' . json_last_error_msg()
                ], 400);
            }
    
            // Validate that the required fields exist
            if (!isset($data['symptoms']) || !isset($data['patientInfo'])) {
                $this->logger->error('Missing required fields', ['data' => $data]);
                return new JsonResponse(['error' => 'Les données symptoms et patientInfo sont requises.'], 400);
            }
    
            // Validate that symptoms are provided and are an array
            if (!is_array($data['symptoms'])) {
                $this->logger->error('Symptoms must be an array', ['symptoms' => $data['symptoms']]);
                return new JsonResponse(['error' => 'Le champ symptoms doit être un tableau.'], 400);
            }
    
            if (empty($data['symptoms'])) {
                $this->logger->error('Symptoms array is empty');
                return new JsonResponse(['error' => 'Veuillez fournir au moins un symptôme.'], 400);
            }
    
            // Flag to switch between direct diagnosis mode and external API call
            $useDirectDiagnosis = true; // Set to false for using external API or service
    
            // Direct diagnosis mode (simplified)
            if ($useDirectDiagnosis) {
                $this->logger->info('Using direct diagnosis mode');
                $directDiagnoses = [
                    'mal de tête' => [
                        'diagnostic' => 'Suspicion de céphalée de tension ou migraine.',
                        'recommendation' => 'Recommandation d\'analgésiques et suivi en cas de persistance.'
                    ],
                    'fièvre' => [
                        'diagnostic' => 'État fébrile possiblement viral.',
                        'recommendation' => 'Surveillance de l\'évolution et traitement symptomatique conseillé.'
                    ],
                    'douleur thoracique' => [
                        'diagnostic' => 'Douleur thoracique à investiguer.',
                        'recommendation' => 'ECG recommandé pour éliminer une cause cardiaque.'
                    ],
                    'vertiges' => [
                        'diagnostic' => 'Vertiges possiblement liés à un trouble vestibulaire ou une hypotension.',
                        'recommendation' => 'Bilan neurologique conseillé.'
                    ],
                    'toux' => [
                        'diagnostic' => 'Toux irritative possiblement liée à une infection des voies respiratoires supérieures.',
                        'recommendation' => 'Traitement symptomatique recommandé.'
                    ],
                    'nausée' => [
                        'diagnostic' => 'Nausées d\'origine possiblement gastrique ou vestibulaire.',
                        'recommendation' => 'Surveillance et traitement symptomatique conseillé.'
                    ]
                ];
    
                $symptom = $data['symptoms'][0];
                $diagnosisData = $directDiagnoses[$symptom] ?? [
                    'diagnostic' => 'Diagnostic impossible à établir.',
                    'recommendation' => 'Consultation médicale recommandée.'
                ];
    
                // Formatage du diagnostic pour l'affichage
                $formattedDiagnosis = $diagnosisData['diagnostic'] . ' ' . $diagnosisData['recommendation'];
    
                $this->logger->info('Direct diagnosis generated', [
                    'symptom' => $symptom, 
                    'diagnosis' => $formattedDiagnosis
                ]);
    
                // Retourner une réponse JSON avec le diagnostic formaté
                return new JsonResponse([
                    'diagnostic' => $formattedDiagnosis
                ]);
            }
    
            // External API diagnosis service (e.g., getDiagnosis service)
            $diagnosis = $this->medicalDiagnosisService->getDiagnosis($data['symptoms'], $data['patientInfo']);
    
            if (!$diagnosis) {
                $this->logger->error('No diagnosis generated by the service', ['diagnosis' => $diagnosis]);
                return new JsonResponse(['error' => 'Le diagnostic ne peut pas être généré avec les données fournies.'], 500);
            }
    
            // Si la réponse est une chaîne, on la retourne directement
            if (is_string($diagnosis)) {
                return new JsonResponse(['diagnostic' => $diagnosis]);
            }
    
            // Si la réponse est un tableau, on tente d'extraire les informations pertinentes
            if (is_array($diagnosis)) {
                if (isset($diagnosis['possibleConditions']) && is_array($diagnosis['possibleConditions'])) {
                    $diagnosisDescriptions = [];
                    foreach ($diagnosis['possibleConditions'] as $condition) {
                        if (isset($condition['condition']) && isset($condition['description'])) {
                            $diagnosisDescriptions[] = $condition['condition'] . ': ' . $condition['description'];
                        } elseif (isset($condition['condition'])) {
                            $diagnosisDescriptions[] = $condition['condition'];
                        }
                    }
                    
                    if (!empty($diagnosisDescriptions)) {
                        return new JsonResponse(['diagnostic' => implode("\n\n", $diagnosisDescriptions)]);
                    }
                }
                
                // Si on ne peut pas extraire des informations structurées, on renvoie tout le diagnostic en JSON
                return new JsonResponse(['diagnostic' => json_encode($diagnosis, JSON_PRETTY_PRINT)]);
            }
    
            // Cas par défaut si aucun format n'est reconnu
            return new JsonResponse(['error' => 'Format de diagnostic non reconnu.'], 500);
    
        } catch (\Exception $e) {
            // Log and handle any exceptions that occur
            $this->logger->error('Error in generate_diagnostic: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
    
            // Return a 500 Internal Server Error with a generic message
            return new JsonResponse(['error' => 'Erreur interne: ' . $e->getMessage()], 500);
        }
    }
}
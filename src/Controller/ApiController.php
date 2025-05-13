<?php

namespace App\Controller;

use App\Service\MedicalDiagnosisService; // This might be removed or refactored later
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController {
    private MedicalDiagnosisService $medicalDiagnosisService; // This might be removed or refactored later
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private string $rapidApiDiagnosisKey;
    private string $rapidApiDiagnosisHost;
    private string $rapidApiDiagnosisUrl;
    private string $rapidApiMedicationKey;
    private string $rapidApiMedicationHost;
    private string $rapidApiMedicationUrl;

    public function __construct(
        MedicalDiagnosisService $medicalDiagnosisService, // This might be removed or refactored later
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        string $rapidApiDiagnosisKey,
        string $rapidApiDiagnosisHost,
        string $rapidApiDiagnosisUrl,
        string $rapidApiMedicationKey,
        string $rapidApiMedicationHost,
        string $rapidApiMedicationUrl
    ) {
        $this->medicalDiagnosisService = $medicalDiagnosisService; // This might be removed or refactored later
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->rapidApiDiagnosisKey = $rapidApiDiagnosisKey;
        $this->rapidApiDiagnosisHost = $rapidApiDiagnosisHost;
        $this->rapidApiDiagnosisUrl = $rapidApiDiagnosisUrl;
        $this->rapidApiMedicationKey = $rapidApiMedicationKey;
        $this->rapidApiMedicationHost = $rapidApiMedicationHost;
        $this->rapidApiMedicationUrl = $rapidApiMedicationUrl;
    }

    #[Route('/api/generate-diagnostic', name: 'generate_diagnostic', methods: ['POST'])]
    public function generateDiagnostic(Request $request): JsonResponse
    {
        try {
            $content = $request->getContent();
            $this->logger->info('API generate_diagnostic called');
            $this->logger->debug('Request content: ' . $content);

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('JSON decoding error: ' . json_last_error_msg());
                return new JsonResponse(['error' => 'Requête invalide. JSON mal formaté: ' . json_last_error_msg()], 400);
            }

            if (!isset($data['symptoms']) || !isset($data['patientInfo'])) {
                $this->logger->error('Missing required fields', ['data' => $data]);
                return new JsonResponse(['error' => 'Les données symptoms et patientInfo sont requises.'], 400);
            }

            if (!is_array($data['symptoms']) || empty($data['symptoms'])) {
                $this->logger->error('Symptoms must be a non-empty array', ['symptoms' => $data['symptoms'] ?? null]);
                return new JsonResponse(['error' => 'Le champ symptoms doit être un tableau non vide.'], 400);
            }
            
            // Ensure patientInfo is an array
            if (!is_array($data['patientInfo'])) {
                 $this->logger->error('patientInfo must be an array', ['patientInfo' => $data['patientInfo']]);
                 return new JsonResponse(['error' => 'Le champ patientInfo doit être un tableau.'], 400);
            }


            $this->logger->info('Calling RapidAPI for diagnosis');
            $response = $this->httpClient->request('POST', $this->rapidApiDiagnosisUrl, [
                'headers' => [
                    'x-rapidapi-host' => $this->rapidApiDiagnosisHost,
                    'x-rapidapi-key' => $this->rapidApiDiagnosisKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'symptoms' => $data['symptoms'],
                    'patientInfo' => $data['patientInfo'],
                    'language' => 'fr', // Added language parameter
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $apiResponseContent = $response->getContent(false); // Get content without throwing on non-2xx
            $this->logger->debug('RapidAPI diagnosis response status: ' . $statusCode);
            $this->logger->debug('RapidAPI diagnosis response content: ' . $apiResponseContent);

            if ($statusCode !== 200) {
                $this->logger->error('RapidAPI diagnosis error', [
                    'statusCode' => $statusCode,
                    'response' => $apiResponseContent,
                ]);
                return new JsonResponse(['error' => 'Erreur lors de la communication avec le service de diagnostic.', 'details' => $apiResponseContent], $statusCode);
            }

            $diagnosisResult = json_decode($apiResponseContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('JSON decoding error for RapidAPI response: ' . json_last_error_msg());
                return new JsonResponse(['error' => 'Réponse invalide du service de diagnostic.'], 500);
            }
            
            // Extract and format diagnosis - this might need adjustment based on actual API response structure
            $formattedDiagnosis = "Diagnostic non disponible.";
            if (isset($diagnosisResult['diagnosis']) && is_array($diagnosisResult['diagnosis'])) {
                $possibleConditions = [];
                foreach($diagnosisResult['diagnosis'] as $diag) {
                    if(isset($diag['conditionName']) && isset($diag['probability'])) {
                        $possibleConditions[] = sprintf("%s (Probabilité: %.2f%%)", $diag['conditionName'], $diag['probability'] * 100);
                    }
                }
                if(!empty($possibleConditions)){
                    $formattedDiagnosis = "Conditions possibles: \n- " . implode("\n- ", $possibleConditions);
                } else if (isset($diagnosisResult['message'])) {
                     $formattedDiagnosis = $diagnosisResult['message'];
                }
            } elseif (isset($diagnosisResult['message'])) {
                 $formattedDiagnosis = $diagnosisResult['message'];
            }


            return new JsonResponse(['diagnostic' => $formattedDiagnosis]);

        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            $this->logger->error('HTTP Transport Error in generate_diagnostic: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Erreur de communication avec le service externe: ' . $e->getMessage()], 503); // Service Unavailable
        } catch (\Exception $e) {
            $this->logger->error('Error in generate_diagnostic: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return new JsonResponse(['error' => 'Erreur interne: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/generate-ordonnance', name: 'generate_ordonnance', methods: ['POST'])]
    public function generateOrdonnance(Request $request): JsonResponse
    {
        try {
            $content = $request->getContent();
            $this->logger->info('API generate_ordonnance called');
            $this->logger->debug('Request content for ordonnance: ' . $content);

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('JSON decoding error for ordonnance: ' . json_last_error_msg());
                return new JsonResponse(['error' => 'Requête invalide. JSON mal formaté: ' . json_last_error_msg()], 400);
            }

            // Validate that the required fields exist (e.g., symptoms or diagnosis)
            // The API documentation suggests 'symptoms' and 'patientInfo' are primary.
            // If 'diagnosis' is passed from a previous step, it might be used or symptoms re-sent.
            // For now, let's assume 'symptoms' and 'patientInfo' are needed as per API docs.
            if (!isset($data['symptoms']) || !isset($data['patientInfo'])) {
                $this->logger->error('Missing required fields for ordonnance', ['data' => $data]);
                return new JsonResponse(['error' => 'Les données symptoms et patientInfo sont requises pour la génération de l\'ordonnance.'], 400);
            }
            
            if (!is_array($data['symptoms']) || empty($data['symptoms'])) {
                 $this->logger->error('Symptoms must be a non-empty array for ordonnance', ['symptoms' => $data['symptoms'] ?? null]);
                 return new JsonResponse(['error' => 'Le champ symptoms doit être un tableau non vide pour l\'ordonnance.'], 400);
            }
            
            if (!is_array($data['patientInfo'])) {
                 $this->logger->error('patientInfo must be an array for ordonnance', ['patientInfo' => $data['patientInfo']]);
                 return new JsonResponse(['error' => 'Le champ patientInfo doit être un tableau pour l\'ordonnance.'], 400);
            }

            $this->logger->info('Calling RapidAPI for medication recommendation');
            $response = $this->httpClient->request('POST', $this->rapidApiMedicationUrl, [
                'headers' => [
                    'x-rapidapi-host' => $this->rapidApiMedicationHost,
                    'x-rapidapi-key' => $this->rapidApiMedicationKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'symptoms' => $data['symptoms'], // Or use 'diagnosis' if the API supports it and it's available
                    'patientInfo' => $data['patientInfo'],
                    'language' => 'fr',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $apiResponseContent = $response->getContent(false);
            $this->logger->debug('RapidAPI medication response status: ' . $statusCode);
            $this->logger->debug('RapidAPI medication response content: ' . $apiResponseContent);

            if ($statusCode !== 200) {
                $this->logger->error('RapidAPI medication error', [
                    'statusCode' => $statusCode,
                    'response' => $apiResponseContent,
                ]);
                return new JsonResponse(['error' => 'Erreur lors de la communication avec le service de recommandation de médicaments.', 'details' => $apiResponseContent], $statusCode);
            }

            $medicationResult = json_decode($apiResponseContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('JSON decoding error for RapidAPI medication response: ' . json_last_error_msg());
                return new JsonResponse(['error' => 'Réponse invalide du service de recommandation de médicaments.'], 500);
            }

            // Extract and format medication recommendations - adjust based on actual API response
            $formattedOrdonnance = "Recommandations non disponibles.";
            if (isset($medicationResult['recommendations']) && is_array($medicationResult['recommendations'])) {
                $recommendations = [];
                foreach($medicationResult['recommendations'] as $rec) {
                    // Example: "Take Paracetamol for fever." Adjust based on actual fields.
                    // Assuming 'medicationName' and 'reason' or similar fields.
                    // This is a placeholder, actual fields from API docs/response are needed.
                    if (isset($rec['recommendationText'])) { // Generic field, check API response
                        $recommendations[] = $rec['recommendationText'];
                    } else if (isset($rec['medication']) && isset($rec['dosage'])) {
                         $recommendations[] = $rec['medication'] . " (" . $rec['dosage'] . ")";
                    }
                }
                 if(!empty($recommendations)){
                    $formattedOrdonnance = "Recommandations: \n- " . implode("\n- ", $recommendations);
                } else if (isset($medicationResult['message'])) {
                    $formattedOrdonnance = $medicationResult['message'];
                }
            } elseif (isset($medicationResult['message'])) {
                $formattedOrdonnance = $medicationResult['message'];
            }


            return new JsonResponse(['ordonnance' => $formattedOrdonnance]);

        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            $this->logger->error('HTTP Transport Error in generate_ordonnance: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Erreur de communication avec le service externe: ' . $e->getMessage()], 503);
        } catch (\Exception $e) {
            $this->logger->error('Error in generate_ordonnance: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return new JsonResponse(['error' => 'Erreur interne: ' . $e->getMessage()], 500);
        }
    }
}
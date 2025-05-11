<?php

namespace App\Service;

class MedicalDiagnosisService {
    private string $rapidApiKey;
    
    public function __construct(string $rapidApiKey = '')
    {
        $this->rapidApiKey = $rapidApiKey;
    }
    
    public function getDiagnosis(array $symptoms, array $patientInfo): ?string
    {
        // Log les données pour débuggage
        $this->logToFile('Symptoms: ' . json_encode($symptoms));
        $this->logToFile('PatientInfo: ' . json_encode($patientInfo));
        
        if (!empty($this->rapidApiKey)) {
            $this->logToFile('API Key (partial): ' . substr($this->rapidApiKey, 0, 5) . '...');
            
            try {
                // Tente d'appeler l'API externe
                $apiResponse = $this->callExternalAPI($symptoms, $patientInfo);
                
                if ($apiResponse && isset($apiResponse['diagnosis'])) {
                    return $apiResponse['diagnosis'];
                }
                
                // Si nous avons des conditions possibles mais pas de diagnostic direct
                if ($apiResponse && isset($apiResponse['possibleConditions']) && !empty($apiResponse['possibleConditions'])) {
                    $diagnosisText = '';
                    foreach ($apiResponse['possibleConditions'] as $condition) {
                        if (isset($condition['condition'])) {
                            $diagnosisText .= $condition['condition'];
                            
                            if (isset($condition['description'])) {
                                $diagnosisText .= ': ' . $condition['description'];
                            }
                            
                            $diagnosisText .= "\n\n";
                        }
                    }
                    
                    if (!empty($diagnosisText)) {
                        return trim($diagnosisText);
                    }
                }
            } catch (\Exception $e) {
                $this->logToFile('API Call Error: ' . $e->getMessage());
                // Continuer avec le fallback
            }
        }
        
        // Fallback - générer un diagnostic simple basé sur les symptômes
        return $this->generateFallbackDiagnosis($symptoms[0] ?? 'symptôme inconnu');
    }
    
    private function callExternalAPI(array $symptoms, array $patientInfo): ?array
    {
        $url = "https://ai-medical-diagnosis-api-symptoms-to-results.p.rapidapi.com/analyzeSymptomsAndDiagnose?noqueue=1";
        
        $data = [
            'symptoms' => $symptoms,
            'patientInfo' => $patientInfo,
            'lang' => 'fr' // Changé à 'fr' pour obtenir le diagnostic en français
        ];
        
        $headers = [
            "X-Rapidapi-Key: {$this->rapidApiKey}",
            "X-Rapidapi-Host: ai-medical-diagnosis-api-symptoms-to-results.p.rapidapi.com",
            "Content-Type: application/json"
        ];
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        // Log la réponse pour débuggage
        $this->logToFile('Response HTTP Code: ' . $httpCode);
        $this->logToFile('Response: ' . $response);
        
        curl_close($curl);
        
        if ($err) {
            $this->logToFile('Curl Error: ' . $err);
            throw new \Exception("Erreur cURL : " . $err);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logToFile('JSON Decode Error: ' . json_last_error_msg());
            throw new \Exception("Erreur de décodage JSON: " . json_last_error_msg());
        }
        
        return $decodedResponse;
    }
    
    private function generateFallbackDiagnosis(string $symptom): string
    {
        // Diagnostic de secours si l'API ne fonctionne pas
        $diagnoses = [
            'mal de tête' => 'Suspicion de céphalée de tension ou migraine. Recommandation d\'analgésiques et suivi en cas de persistance.',
            'fièvre' => 'État fébrile possiblement viral. Surveillance de l\'évolution et traitement symptomatique conseillé.',
            'douleur thoracique' => 'Douleur thoracique à investiguer. ECG recommandé pour éliminer une cause cardiaque.',
            'vertiges' => 'Vertiges possiblement liés à un trouble vestibulaire ou une hypotension. Bilan neurologique conseillé.',
            'toux' => 'Toux irritative possiblement liée à une infection des voies respiratoires supérieures. Traitement symptomatique recommandé.',
            'nausée' => 'Nausées d\'origine possiblement gastrique ou vestibulaire. Surveillance et traitement symptomatique conseillé.'
        ];
        
        return $diagnoses[$symptom] ?? 'Diagnostic impossible à établir avec les données fournies. Consultation médicale approfondie recommandée.';
    }
    
    private function logToFile(string $message): void
    {
        $logFile = __DIR__ . '/../../var/log/medical_service.log';
        
        // S'assurer que le répertoire des logs existe
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    }
}
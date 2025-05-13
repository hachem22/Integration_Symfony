<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class AIDoctorApiService
{
    private HttpClientInterface $client;
    private string $apiUrl = 'https://ai-doctor-api-ai-medical-chatbot-healthcare-ai-assistant.p.rapidapi.com/chat';
    private string $apiHost = 'ai-doctor-api-ai-medical-chatbot-healthcare-ai-assistant.p.rapidapi.com';
    private string $apiKey = '63155ea07cmshdc823bd2aadfd07p14afd9jsn6ebbd855e7cf'; // Replace with your key

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getMedicalResponse(string $message, string $specialization, string $language): array
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' => $this->apiHost,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => $message,
                    'specialization' => strtolower($specialization),
                    'language' => strtolower($language),
                ],
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return ['error' => 'API Error (' . $response->getStatusCode() . '): ' . $response->getContent(false)];
            }

            return $this->processApiResponse($response->toArray());

        } catch (\Exception $e) {
            return ['error' => 'Network Error: ' . $e->getMessage()];
        }
    }

    private function processApiResponse(array $response): array
    {
        if (!isset($response['result'])) {
            return ['error' => 'Invalid API response structure'];
        }

        $result = $response['result'];
        $responseData = $result['response'] ?? [];
        $metadata = $result['metadata'] ?? [];

        return [
            'message' => $responseData['message'] ?? null,
            'recommendations' => $responseData['recommendations'] ?? [],
            'warnings' => $responseData['warnings'] ?? [],
            'requiresPhysicianConsult' => $metadata['requiresPhysicianConsult'] ?? null,
            'emergencyLevel' => $metadata['emergencyLevel'] ?? null,
            'topRelatedSpecialties' => $metadata['topRelatedSpecialties'] ?? [],
        ];
    }
}
<?php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GeminiService
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function generateCommentaire(string $description): string
    {
        $client = new Client();
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";
        $descriptionEnFrancais = "Génère un commentaire basé sur la description suivante de la réclamation : " . $description;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $descriptionEnFrancais]
                    ]
                ]
            ]
        ];

        try {
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            // Ajouter un log pour voir la réponse brute
            error_log("Réponse de l'API Gemini : " . print_r($responseData, true));

            return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Erreur lors de la génération de la description.';
        } catch (GuzzleException $e) {
            throw new \Exception('Erreur lors de la génération de la description : ' . $e->getMessage());
        }
    }

}

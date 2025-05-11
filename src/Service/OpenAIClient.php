<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIClient
{
    private string $apiKey;
    private HttpClientInterface $httpClient;

    public function __construct(string $apiKey, HttpClientInterface $httpClient)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
    }

    public function getAutoCompleteSuggestion(string $input): ?string
    {
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo', // ou 'text-davinci-003'
                'prompt' => "Complète cette phrase de manière appropriée : " . $input,
                'max_tokens' => 50,
                'temperature' => 0.7,
            ],
        ]);

        $data = $response->toArray();

        return $data['choices'][0]['text'] ?? null;
    }
}

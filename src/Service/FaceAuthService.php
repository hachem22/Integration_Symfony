<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceAuthService {
    private $client;

    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }

    public function authenticateFace(): ?array
    {
        try {
            $response = $this->client->request('GET', 'http://127.0.0.1:5000/recognize');
            $data = $response->toArray(); // Convert response to array

            // Ensure the response contains email and password
            if (!isset($data['email']) || !isset($data['password'])) {
                return ['error' => 'Invalid response from Flask API: Missing email or password'];
            }

            return $data; // Return the recognized face data (email and password)
        } catch (\Exception $e) {
            // Handle the error
            return ['error' => 'Error calling Flask API: ' . $e->getMessage()];
        }
    }
}
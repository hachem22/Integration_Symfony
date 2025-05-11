<?php

namespace App\Controller;

use App\Service\AIDoctorApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    private AIDoctorApiService $apiService;

    public function __construct(AIDoctorApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    #[Route('/chatbot_api/send_message', name: 'chatbot_send_message', methods: ['POST'])]
    public function handleChatMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';
        // You might want to get specialization and language from user session or a default
        $specialization = $data['specialization'] ?? 'general'; // Default to general
        $language = $data['language'] ?? 'fr'; // Default to French

        if (empty($userMessage)) {
            return new JsonResponse(['error' => 'Message cannot be empty.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $apiResponse = $this->apiService->getMedicalResponse($userMessage, $specialization, $language);

        return new JsonResponse($apiResponse);
    }
}
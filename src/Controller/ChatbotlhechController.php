<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\ChatbotService;
use Doctrine\ORM\NonUniqueResultException;

final class ChatbotlhechController extends AbstractController{
    private $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    #[Route('/chatbot-ui', name: 'chatbot_ui')]
    public function chatbotUi()
    {
        return $this->render('chatbot.html.twig');
    }

    #[Route('/chatbot', name: 'chatbot', methods: ['POST'])]
public function chatbot(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifiez si la clé "message" existe
    if (!isset($data['message'])) {
        return new JsonResponse(['error' => 'La clé "message" est manquante dans la requête.'], 400);
    }

    $message = $data['message'];

    try {
        $response = $this->chatbotService->analyserSymptomes($message);
    } catch (NonUniqueResultException $e) {
        return new JsonResponse(['error' => 'Multiple results found where one was expected.'], 500);
    }

    return new JsonResponse(['response' => $response]);
}
}

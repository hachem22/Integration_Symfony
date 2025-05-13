<?php
namespace App\Controller;

use App\Service\ChatbotHospitalierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ChatBotaziz extends AbstractController
{
    private $chatbotService;

    public function __construct(ChatbotHospitalierService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    #[Route('/chatbot', name: 'chatbot_interact', methods: ['POST'])]
public function interagirChatbot(Request $request): JsonResponse
{
    $donnees = json_decode($request->getContent(), true);

    // Validation basique
    if (!isset($donnees['requete'])) {
        return new JsonResponse([
            'statut' => 'erreur',
            'message' => 'Requête manquante'
        ], 400);
    }

    try {
        $resultat = $this->chatbotService->traiterRequete($donnees['requete']);
        return new JsonResponse($resultat);
    } catch (\Exception $e) {
        return new JsonResponse([
            'statut' => 'erreur',
            'message' => 'Erreur lors du traitement : ' . $e->getMessage()
        ], 500);
    }
}

    #[Route('/chatbot/interface', name: 'chatbot_interface')]
    public function interfaceChatbot(): Response
    {
        return $this->render('chatbotaziz/interface.html.twig');
    }
}
<?php
// src/Controller/OrdonnanceApiController.php
namespace App\Controller;

use App\Service\OrdonnanceAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrdonnanceApiController extends AbstractController
{
    #[Route('/api/ordonnance', name: 'api_ordonnance', methods: ['POST'])]
    public function generateOrdonnance(Request $request, OrdonnanceAIService $ordonnanceService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $maladie = $data['maladie'] ?? '';
        
        if (empty($maladie)) {
            return $this->json(['error' => 'Aucune maladie spécifiée'], 400);
        }
        
        $ordonnance = $ordonnanceService->generateOrdonnance($maladie);
        
        return $this->json([
            'ordonnance' => $ordonnance
        ]);
    }
}
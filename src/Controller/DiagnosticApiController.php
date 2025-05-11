<?php
// src/Controller/DiagnosticApiController.php
namespace App\Controller;

use App\Service\DiagnosticAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DiagnosticApiController extends AbstractController
{
    #[Route('/api/diagnostic', name: 'api_diagnostic', methods: ['POST'])]
    public function generateDiagnostic(Request $request, DiagnosticAIService $diagnosticService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $maladie = $data['maladie'] ?? '';
        
        if (empty($maladie)) {
            return $this->json(['error' => 'Aucune maladie spécifiée'], 400);
        }
        
        $diagnostic = $diagnosticService->generateDiagnostic($maladie);
        
        return $this->json([
            'diagnostic' => $diagnostic
        ]);
    }
}
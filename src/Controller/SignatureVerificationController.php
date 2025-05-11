<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class SignatureVerificationController extends AbstractController
{
    #[Route('/verify-signature', name: 'app_verify_signature', methods: ['POST'])]
    public function verifySignature(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('signature_file');
        
        if (!$uploadedFile) {
            return new JsonResponse([
                'match' => false,
                'error' => 'Aucun fichier téléchargé'
            ]);
        }

        try {
            // Définir le répertoire de signature dans le dossier public
            $signatureDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/signatures';
            $referenceImagePath = $signatureDirectory . '/signature.jpg';
            
            // Vérifier et créer le répertoire si nécessaire
            if (!file_exists($signatureDirectory)) {
                mkdir($signatureDirectory, 0777, true);
            }

            // Si l'image de référence n'existe pas
            if (!file_exists($referenceImagePath)) {
                try {
                    $uploadedFile->move($signatureDirectory, 'signature.jpg');
                    return new JsonResponse([
                        'match' => true,
                        'message' => 'Image de référence créée avec succès'
                    ]);
                } catch (FileException $e) {
                    return new JsonResponse([
                        'match' => false,
                        'error' => 'Erreur lors de la création de l\'image de référence'
                    ]);
                }
            }

            // Comparaison plus robuste des images
            $uploadedImageHash = hash_file('sha256', $uploadedFile->getPathname());
            $referenceImageHash = hash_file('sha256', $referenceImagePath);

            $match = $uploadedImageHash === $referenceImageHash;

            return new JsonResponse([
                'match' => $match,
                'message' => $match ? 'Signature valide' : 'Signature non valide'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'match' => false,
                'error' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ]);
        }
    }
} 
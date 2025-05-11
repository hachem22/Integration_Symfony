<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Lit;
use App\Entity\Chambre;

class ExportController extends AbstractController
{
    #[Route('/export/statistiques/csv', name: 'export_statistiques_csv')]
    public function exportStatistiquesCsv(EntityManagerInterface $entityManager): Response
    {
        // Récupération des statistiques depuis la base de données
        $litsOccupes = $entityManager->getRepository(Lit::class)->count(['type' => 'occupé']);
        $litsDisponibles = $entityManager->getRepository(Lit::class)->count(['type' => 'libre']);
        $chambresOccupees = $entityManager->getRepository(Chambre::class)->count(['active' => false]);
        $chambresDisponibles = $entityManager->getRepository(Chambre::class)->count(['active' => true]);

        // Données des statistiques
        $data = [
            ['Type', 'Nombre'],
            ['Lits Occupés', $litsOccupes],
            ['Lits Disponibles', $litsDisponibles],
            ['Chambres Occupées', $chambresOccupees],
            ['Chambres Disponibles', $chambresDisponibles],
        ];

        // Création d'une réponse CSV en streaming
        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            foreach ($data as $row) {
                fputcsv($handle, $row, ';'); // Séparateur ";"
            }

            fclose($handle);
        });

        // Configuration des en-têtes HTTP pour le téléchargement
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="statistiques.csv"');

        return $response;
    }
}

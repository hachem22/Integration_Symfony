<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EvenementRepository;
use App\Repository\ReclamationRepository;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Alignment\LabelAlignment;
use Endroid\QrCode\Label\Font\NotoSans;


class PatientDashboardController extends AbstractController
{
    #[Route('/patient/dashboard', name: 'app_patient_dashboard')]
    public function index(EvenementRepository $evenementRepository): Response
    {
        // Récupère tous les événements
        $evenements = $evenementRepository->findAll();

        // Tableau pour stocker les QR codes
        $qrCodes = [];

        foreach ($evenements as $event) {
            // Crée une chaîne de texte avec le nom et la capacité de l'événement
            $eventData = "Event Name: ". $event->getNom();
            $eventData .= "Descripion: " . $event->getDescription();
            // Crée un QR code avec ces informations
            $qrCode = new QrCode($eventData);
           // $qrCode->setSize(300);
        
            // Convertit l'image du QR code en base64 avec la méthode write()
            $writer = new PngWriter();
            $qrImage = $writer->write($qrCode);  // Utilisation de write() ici

            // Stocke le QR code encodé en base64 dans le tableau
            $qrCodes[$event->getId()] = base64_encode($qrImage->getString()); // Utilisation de getString() pour obtenir l'image binaire
        }


        return $this->render('patient/dashboard.html.twig', [
            'user' => $this->getUser(),
'evenements' => $evenements,
            'qrCodes' => $qrCodes,        ]);
    }
}
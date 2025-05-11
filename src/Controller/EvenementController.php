<?php

namespace App\Controller;
use App\Service\HuggingFaceService;
use App\Service\WeatherService;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\Nour;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Evenement;
use App\Entity\Formation;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Alignment\LabelAlignment;
use Endroid\QrCode\Label\Font\NotoSans;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    private $weatherService;
    private $evenementRepository;
    private LoggerInterface $logger;
    // Affiche la liste des événements, triés par ordre alphabétique et recherche
    #[Route('/', name: 'evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository, EntityManagerInterface $entityManager): Response
{
    // Récupérer les paramètres de recherche et de formation
    $evenement = $evenementRepository->findAll();

    $searchTerm = $request->query->get('search', ''); // Terme de recherche
    $formationSearch = $request->query->get('formation', ''); // Formation recherchée

    // Récupérer l'ordre de tri uniquement si le paramètre 'sort' est présent dans la requête
    $sortOrder = $request->query->get('sort', null); // Par défaut, pas de tri

    // Appliquer la recherche et le tri
    $criteria = [];
    if ($searchTerm) {
        $criteria['Nom'] = $searchTerm;
    }

    if ($formationSearch) {
        $criteria['formations'] = $formationSearch;
    }

    // Récupérer les événements avec recherche et tri, incluant les formations associées
    if ($sortOrder) {
        $evenements = $evenementRepository->findBy($criteria, ['Nom' => $sortOrder]);
    } else {
        // Si aucun tri n'est sélectionné, ne pas appliquer de tri
        $evenements = $evenementRepository->findBy($criteria);
    }

    // Passer toutes les formations à la vue
    $formations = $entityManager->getRepository(Formation::class)->findAll();

    return $this->render('admin/index.html.twig', [
        'evenements' => $evenements,
        'searchTerm' => $searchTerm, // Passer le terme de recherche pour pré-remplir le champ
        'formationSearch' => $formationSearch, // Passer la formation recherchée
        'formations' => $formations,  // Passer toutes les formations disponibles à la vue
    ]);
}


    // Affiche le formulaire pour créer un nouvel événement
    #[Route('/new', name: 'evenement_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    LoggerInterface $logger,
    MessageBusInterface $bus
): Response {
    $evenement = new Evenement();
    $form = $this->createForm(EvenementType::class, $evenement);
    $form->handleRequest($request);

    $logger->info('Formulaire soumis: ' . ($form->isSubmitted() ? 'Oui' : 'Non'));

    if ($form->isSubmitted() && $form->isValid()) {
        $logger->info('Formulaire valide, traitement en cours.');

        // Récupérer les formations sélectionnées
        $formationsIds = $request->get('formations', []); 
        foreach ($formationsIds as $formationId) {
            $formation = $entityManager->getRepository(Formation::class)->find($formationId);
            if ($formation) {
                $formation->setEvenement($evenement);
                $entityManager->persist($formation);
                $logger->info('Formation associée: ' . $formation->getNom());
            } else {
                $logger->warning('Formation non trouvée: ' . $formationId);
            }
        }

        $entityManager->persist($evenement);
        $entityManager->flush();
        $logger->info('Détails de l\'événement : ' . json_encode([
            'Nom' => $evenement->getNom(),
            'Description' => $evenement->getDescription(),
            'Capacité' => $evenement->getCapacite(),
            'Lieu' => $evenement->getLieu(),
        ]));

        $logger->info('Événement ajouté avec succès: ' . $evenement->getNom());

        // Envoi de l'email de confirmation
        $utilisateur = $evenement->getUtilisateurevent();
        $email = $utilisateur ? $utilisateur->getEmail() : null;

        if ($email) {
            // Créer une instance de Nour avec les informations nécessaires
            $message = new Nour(
                $utilisateur->getEmail(),    // Email
                $utilisateur->getNom(),      // Nom
                'password_placeholder'       // Mot de passe temporaire (remplacer si nécessaire)
            );

            // Envoyer le message via le bus
            $bus->dispatch($message);
            $logger->info('Email de confirmation envoyé à: ' . $email);
        } else {
            $logger->warning('Aucun email utilisateur trouvé, email non envoyé.');
        }

        return $this->redirectToRoute('evenement_index');
    }

    // Passer les formations à la vue
    $formations = $entityManager->getRepository(Formation::class)->findAll();

    return $this->render('admin/new.html.twig', [
        'evenement' => $evenement,
        'form' => $form->createView(),
        'formations' => $formations,
    ]);
}


    // Affiche les détails d'un événement
    #[Route('/{id}', name: 'evenement_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(EvenementRepository $evenementRepository, int $id): Response
    {
        $evenement = $evenementRepository->find($id);

        if (!$evenement) {
            throw new NotFoundHttpException('Événement non trouvé');
        }

        return $this->render('admin/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    // Édite un événement existant
    #[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('evenement_index');
        }

        // Passer les formations à la vue
        $formations = $entityManager->getRepository(Formation::class)->findAll();

        return $this->render('admin/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
            'formations' => $formations,  // Passer les formations à la vue
        ]);
    }

    // Supprime un événement
    #[Route('/{id}', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('evenement_index');
    }

   
    
    #[Route('/cards', name: 'app_evenement_cards')]
    public function cards(EvenementRepository $evenementRepository): Response
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
            //$qrCode->setSize(300);
        
            // Convertit l'image du QR code en base64 avec la méthode write()
            $writer = new PngWriter();
            $qrImage = $writer->write($qrCode);  // Utilisation de write() ici

            // Stocke le QR code encodé en base64 dans le tableau
            $qrCodes[$event->getId()] = base64_encode($qrImage->getString()); // Utilisation de getString() pour obtenir l'image binaire
        }

        // Passe les événements et QR codes à la vue
        return $this->render('patient/dashboard.html.twig', [
            'evenements' => $evenements,
            'qrCodes' => $qrCodes,
        ]);
    }

    /**
     * @Route("/details/{id}", name="app_evenement_show")
     */
    #[Route('/details/{id}', name: 'app_evenement_show')]
    public function showw(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }
    #[Route('/search', name: 'evenement_search', methods: ['GET'])]
public function search(Request $request, EvenementRepository $evenementRepository): JsonResponse
{
    $searchTerm = $request->query->get('search', '');

    if (!$searchTerm) {
        return new JsonResponse(['error' => 'Veuillez saisir un nom d\'événement'], Response::HTTP_BAD_REQUEST);
    }

    $queryBuilder = $evenementRepository->createQueryBuilder('e');
    $queryBuilder
        ->andWhere('e.Nom LIKE :se')
        ->setParameter('se', '%'.$searchTerm.'%');

    $evenements = $queryBuilder->getQuery()->getResult();

    if (empty($evenements)) {
        return new JsonResponse(['error' => 'Aucun événement trouvé'], Response::HTTP_NOT_FOUND);
    }

    $result = [];
    foreach ($evenements as $evenement) {
        $result[] = [
            'nom' => $evenement->getNom(),
            'description' => $evenement->getDescription(),
            'lieu' => $evenement->getLieu(),
        ];
    }

    return new JsonResponse($result);
}


   


    
    #[Route('/generate-qr-code', name: 'generate_qr_code')]
    public function generateQrCode(): Response
    {
        // Données simples
        $qrCode = new QrCode('Hello Symfony');
        $writer = new PngWriter();
        
        // Utilisation de la méthode 'write' pour générer l'image
        $qrImage = $writer->write($qrCode);
        
        return new Response($qrImage->getString(), 200, [
            'Content-Type' => 'image/png',
        ]);
    }

    #[Route('/evenement/{id}/download-pdf', name: 'evenement_download_pdf')]
    public function downloadPdf(Evenement $evenement): Response
    {
        // Vérifier que l'événement existe
        if (!$evenement) {
            throw $this->createNotFoundException("Événement introuvable !");
        }

        // Vérification des formations
        $formations = $evenement->getFormations();
        $formationNom = "N/A"; // Valeur par défaut

        if ($formations) {
            if (is_iterable($formations)) {
                $formationNom = implode(", ", array_map(fn($f) => $f->getNom(), $formations->toArray()));
            } else {
                $formationNom = $formations->getNom();
            }
        }

        // Initialisation de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        // Générer le contenu HTML
        $htmlContent = $this->renderView('evenement/pdf_template.html.twig', [
            'evenement' => $evenement,
            'formationNom' => $formationNom
        ]);

        // Charger le contenu HTML dans Dompdf
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le fichier PDF
        $pdfOutput = $dompdf->output();
        $pdfFilename = "evenement_{$evenement->getId()}.pdf";

        // Retourner le fichier en téléchargement
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfFilename.'"',
        ]);
    }
}
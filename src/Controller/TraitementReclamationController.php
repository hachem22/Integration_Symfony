<?php

namespace App\Controller;

use App\Entity\TraitementReclamation;
use App\Entity\Reclamation;
use App\Form\TraitementReclamationType;
use App\Repository\TraitementReclamationRepository;
use App\Enum\ReclamationStatut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\GeminiService;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;


use Twilio\Rest\Client;


#[Route('/traitement')]
class TraitementReclamationController extends AbstractController
{
     
    private $entityManager;
    private $geminiService;

    // Injection de EntityManagerInterface et GeminiService via le constructeur
    public function __construct(EntityManagerInterface $entityManager, GeminiService $geminiService)
    {
        $this->entityManager = $entityManager;
        $this->geminiService = $geminiService;
    }

    

    #[Route('/new/{id}', name: 'app_traitement_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        Client $twilio // Injection du service Twilio
    ): Response {
        if (!$reclamation) {
            throw $this->createNotFoundException("Réclamation introuvable !");
        }

        $traitement = new TraitementReclamation();
        $traitement->setReclamation($reclamation);
        $traitement->setDateTraitement(new \DateTime());
        $traitement->setEtat(ReclamationStatut::fromString('EN_COURS'));

        $form = $this->createForm(TraitementReclamationType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($traitement);

            // Mise à jour du statut de la réclamation
            if ($traitement->getEtat()->value === ReclamationStatut::RESOLUE->value) {
                $reclamation->setStatut(ReclamationStatut::RESOLUE);
            } elseif ($traitement->getEtat()->value === ReclamationStatut::REJETE->value) {
                $reclamation->setStatut(ReclamationStatut::REJETE);
            } else {
                $reclamation->setStatut(ReclamationStatut::EN_COURS);
            }

            $em->flush();

            // Récupérer le nom et l'email de l'utilisateur
            $userEmail = $reclamation->getUtilisateur()->getEmail();
            $userName = $reclamation->getUtilisateur()->getNom(); // Supposons que getNom() retourne le nom de l'utilisateur

            // Envoyer un email à l'utilisateur
            $emailContent = sprintf(
                "Bonjour %s,\n\n" .
                "Nous tenons à vous informer que votre réclamation a été traitée. Voici les détails du traitement :\n\n" .
                "- Commentaire : %s\n" .
                "- Statut : %s\n" .
                "- Date du traitement : %s\n\n" .
                "Nous restons à votre disposition pour toute information complémentaire.\n\n" .
                "Cordialement,\n" .
                "L'équipe de support",
                $userName,
                $traitement->getCommentaire(),
                $traitement->getEtat()->value,
                $traitement->getDateTraitement()->format('Y-m-d H:i:s')
            );

            $email = (new Email())
                ->from('admin@example.com')
                ->to($userEmail)
                ->subject('Réclamation traitée')
                ->text($emailContent);

            $mailer->send($email);

            // Envoyer un SMS à l'utilisateur
           $phoneUtil = PhoneNumberUtil::getInstance();

try {
    $numberProto = $phoneUtil->parse($reclamation->getUtilisateur()->getTel(), "TN"); // "TN" = Tunisie
    $userPhoneNumber = $phoneUtil->format($numberProto, PhoneNumberFormat::E164);

    $smsContent = sprintf(
        "Bonjour %s,\n\nVotre réclamation a été traitée. Statut : %s.\n\nCordialement,\nL'équipe de support",
        $userName,
        $traitement->getEtat()->value
    );

    $twilio->messages->create(
        $userPhoneNumber,
        [
            'from' => $_ENV['TWILIO_PHONE_NUMBER'],
            'body' => $smsContent
        ]
    );
    $this->addFlash('success', 'SMS envoyé avec succès.');
} catch (NumberParseException $e) {
    $this->addFlash('danger', 'Numéro de téléphone invalide.');
} catch (\Twilio\Exceptions\RestException $e) {
    $this->addFlash('danger', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
}

            $this->addFlash('success', 'Traitement ajouté avec succès.');
            return $this->redirectToRoute('app_traitement_index');
        }

        return $this->render('traitement_reclamation/new.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation
        ]);
    }

    #[Route('/', name: 'app_traitement_index')]
    public function index(Request $request, TraitementReclamationRepository $repo, EntityManagerInterface $em): Response
    {
        $etat = $request->query->get('etat', ''); // Récupération de l'état depuis l'URL
    
        // Filtrer les traitements selon l'état s'il est défini
        if ($etat) {
            $traitements = $repo->searchTraitementsParEtat($etat);
        } else {
            $traitements = $repo->findAll();
        }
    
        // Récupérer la réclamation associée au premier traitement trouvé
        $reclamation = !empty($traitements) ? $traitements[0]->getReclamation() : null;
    
        return $this->render('traitement_reclamation/index.html.twig', [
            'traitements' => $traitements,
            'reclamation' => $reclamation,
            'selectedEtat' => $etat, // Permet de pré-remplir le filtre dans le template
        ]);
    }
    
    #[Route('/edit/{id}', name: 'app_traitement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TraitementReclamation $traitement, EntityManagerInterface $em): Response
    {
        // Récupérer la réclamation associée
        $reclamation = $traitement->getReclamation(); // Assurez-vous que cette relation existe dans l'entité
    
        $form = $this->createForm(TraitementReclamationType::class, $traitement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour du statut de la réclamation
            if ($traitement->getEtat() === ReclamationStatut::RESOLUE || $traitement->getEtat() === ReclamationStatut::REJETE) {
                $reclamation->setStatut($traitement->getEtat());
            } else {
                $reclamation->setStatut(ReclamationStatut::EN_COURS);
            }
        
            $em->flush();
            $this->addFlash('success', 'Traitement modifié avec succès.');
            return $this->redirectToRoute('app_traitement_index');
        }
        return $this->render('traitement_reclamation/edit.html.twig', [
            'form' => $form->createView(),
            'traitement' => $traitement,
            'reclamation' => $reclamation,
        ]);
        
    }
    
    #[Route('/delete/{id}', name: 'app_traitement_delete', methods: ['POST'])]
    public function delete(Request $request, TraitementReclamation $traitement, EntityManagerInterface $em): Response
    {
        if (!$traitement) {
            throw $this->createNotFoundException("Traitement introuvable !");
        }

        if ($this->isCsrfTokenValid('delete'.$traitement->getId(), $request->request->get('_token'))) {
            $em->remove($traitement);
            $em->flush();
            $this->addFlash('success', 'Traitement supprimé avec succès.');
        } else {
            $this->addFlash('danger', 'Échec de la suppression du traitement.');
        }

        return $this->redirectToRoute('app_traitement_index');
    }
    #[Route('/traitement/{id}', name: 'app_traitement_show', methods: ['GET'])]
    public function show(TraitementReclamation $traitement): Response
    {
        $reclamation = $traitement->getReclamation();
        return $this->render('traitement_reclamation/show.html.twig', [
            'traitement' => $traitement,
            'reclamation' => $reclamation,
        ]);
    }
    #[Route('/traitement/{id}/rating-stats', name: 'app_traitement_rating_stats', methods: ['GET'])]
    public function ratingStats(TraitementReclamation $traitement, TraitementReclamationRepository $traitementRepo): Response
    {
        // Récupérer les données pour le graphique
        $ratingData = $traitementRepo->countTraitementsByRating();
    
        // Formater les données pour Chart.js
        $labels = [];
        $data = [];
        foreach ($ratingData as $item) {
            $labels[] = $item['rating'] . ' étoiles';
            $data[] = $item['count'];
        }
    
        return $this->render('traitement_reclamation/rating_stats.html.twig', [
            'labels' => $labels,
            'data' => $data,
            'traitement' => $traitement, // Passer le traitement à la vue
        ]);
    }
    #[Route('/reclamation/generate-comment', name: 'generate_commentaire', methods: ['POST'])]
    public function generateCommentaire(Request $request): JsonResponse
    {
        // Récupérer l'ID de la réclamation à partir de la requête
        $reclamationId = $request->request->get('reclamationId');
        
        // Utiliser Doctrine pour obtenir la réclamation
        $reclamation = $this->entityManager->getRepository(Reclamation::class)->find($reclamationId);
    
        if (!$reclamation) {
            return new JsonResponse(['error' => 'Réclamation non trouvée'], 404);
        }
    
        // Récupérer la description de la réclamation
        $description = $reclamation->getDescription();
    
        // Appeler le service Gemini pour générer un commentaire basé sur la description
        try {
            $generatedComment = $this->geminiService->generateCommentaire($description);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la génération du commentaire : ' . $e->getMessage()], 500);
        }
    
        // Retourner la réponse avec le commentaire généré
        return new JsonResponse(['commentaire' => $generatedComment]);
    }
    

}

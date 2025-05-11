<?php

namespace App\Controller;

use App\Repository\PlanningRepository;
use App\Entity\Planning;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\UtilisateurRepository;
use App\Entity\Utilisateur;
use App\Form\PlanningType;
use App\Form\DateNonDisponibleType;

final class PlanningController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

   #[Route('/dates-disponibles/{medecinId}', name: 'dates_disponibles', methods: ['GET'])]
    public function getDatesDisponibles(int $medecinId, PlanningRepository $planningRepository): JsonResponse
    {
        $plannings = $planningRepository->findByMedecin($medecinId);

        $datesDisponibles = [];
        foreach ($plannings as $planning) {
            $datesDisponibles[] = [
                'date' => $planning->getDateDisponible()->format('Y-m-d'),
            ];
        }

        return $this->json($datesDisponibles);
    }
    #[Route('/planning', name: 'planning_index')]
    public function heure(PlanningRepository $planningRepository): Response
    {
        return $this->render('medecin/planning.html.twig', [
            'plannings' => $planningRepository->findAll(),
        ]);
    }
    #[Route('/planning/create', name: 'planning_create')]

    public function create(Request $request, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response // Injection de EntityManagerInterface
    {
        $planning = new Planning();
        $form = $this->createForm(PlanningType::class, $planning);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $planning->setMedecin($this->getUser()); // Assumer que l'utilisateur connecté est un médecin
            
            // Utilisation du gestionnaire d'entités injecté
            $entityManager->persist($planning);
            $entityManager->flush();

            $this->addFlash('success', 'Planning créé avec succès !');
            return $this->redirectToRoute('planning_index');
        }

        return $this->render('medecin/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/ajouter-date-non-disponible', name: 'ajouter_date_non_disponible')]
    public function ajouterDateNonDisponible(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        PlanningRepository $planningRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Créer le formulaire
        $form = $this->createForm(DateNonDisponibleType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $medecin = $form->get('medecin')->getData();
            $dateNonDisponible = $form->get('dateNonDisponible')->getData();

            // Vérifier si un planning existe déjà pour ce médecin
            $planning = $planningRepository->findOneBy(['medecin' => $medecin]);

            if (!$planning) {
                // Créer un nouveau planning si aucun n'existe
                $planning = new Planning();
                $planning->setMedecin($medecin);
            }

            // Ajouter la date non disponible
            $planning->ajouterDateNonDisponible($dateNonDisponible->format('Y-m-d'));

            // Enregistrer en base de données
            $entityManager->persist($planning);
            $entityManager->flush();

            // Ajouter un message flash de succès
            $this->addFlash('success', 'Date non disponible ajoutée avec succès.');

            // Rediriger vers la même page pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('ajouter_date_non_disponible');
        }

        // Afficher le formulaire
        return $this->render('medecin/ajouter_date_non_disponible.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/calendrier', name: 'calendrier')]
public function index(
    Request $request,
    PlanningRepository $planningRepository,
    Security $security
): Response {
    // Récupérer le médecin connecté
    $medecinConnecte = $security->getUser();
    if (!$medecinConnecte instanceof Utilisateur) {
        throw $this->createAccessDeniedException('Vous devez être connecté en tant que médecin pour accéder à cette page.');
    }
    
    if (!$medecinConnecte) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
    }
    
    // Récupérer uniquement les plannings du médecin connecté
    $plannings = $planningRepository->findBy(['medecin' => $medecinConnecte->getId()]);
    
    // Préparer les données pour le calendrier
    $events = [];
    foreach ($plannings as $planning) {
        // Ajouter les dates non disponibles
        foreach ($planning->getDatesNonDisponibles() as $dateNonDisponible) {
            $events[] = [
                'title' => 'Date non disponible',
                'start' => $dateNonDisponible,
                'display' => 'background',
                'backgroundColor' => 'red',
            ];
        }
        
        // Ajouter les heures réservées
        if ($planning->getDateDisponible()) {
            $dateDisponible = $planning->getDateDisponible()->format('Y-m-d');
            $tempsReserver = $planning->getTempsReserver();
            $allTimes = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
            
            foreach ($tempsReserver as $heureReservee) {
                $events[] = [
                    'title' => 'Réservé',
                    'start' => $dateDisponible . 'T' . $heureReservee,
                    'color' => 'green',
                ];
            }
            
            if (count($tempsReserver) == count($allTimes)) {
                $events[] = [
                    'title' => 'Toutes les heures réservées',
                    'start' => $dateDisponible,
                    'display' => 'background',
                    'backgroundColor' => 'yellow',
                ];
            }
        }
    }
    
    return $this->render('medecin/calendrier.html.twig', [
        'events' => json_encode($events),
        'medecinConnecte' => $medecinConnecte
    ]);
}
}

<?php

// RendezVousController.php
namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Utilisateur;
use App\Form\RendezVousType;
use App\Form\MedecinRendezVousType;
use App\Form\HeureRendezVousType;

use App\Entity\Planning;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\ServiceRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\PlanningRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Enum\RendezVousStatus;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\RendezVousRepository;
use App\Form\HeureType;
use App\Form\RendezVousModifierType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;


use Symfony\Component\Routing\Annotation\Route;

class RendezVousController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    

    #[Route('/demande-rendezvous', name: 'demande_rendezvous')]
    public function demandeRendezVous(
        Request $request,
        ServiceRepository $serviceRepository,
        PlanningRepository $planningRepository,
        EntityManagerInterface $entityManager,
        Security $security // Ajout de Security pour récupérer l'utilisateur connecté
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si l'utilisateur est connecté et s'il est un patient
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        // Récupérer tous les services
        $services = $serviceRepository->findAll();

        if (empty($services)) {
            $this->addFlash('warning', 'Aucun service disponible pour le moment.');
            return $this->render('patient/demande.html.twig', [
                'form' => null,
            ]);
        }

        // Créer une nouvelle instance de RendezVous
        $rendezVous = new RendezVous();
        $rendezVous->setRendezVousStatus('EN_ATTENTE');
        $rendezVous->setPatient($user); // Associer l'utilisateur connecté en tant que patient

        // Créer le formulaire
        $form = $this->createForm(RendezVousType::class, $rendezVous, [
            'services' => $services,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la date sélectionnée
            $dateSelectionnee = $form->get('date')->getData();
            $medecinSelectionne = $form->get('medecin')->getData();

            if (!$dateSelectionnee) {
                $this->addFlash('error', 'Veuillez sélectionner une date.');
                return $this->render('patient/demande.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            if (in_array($dateSelectionnee->format('N'), [6, 7])) {
                $this->addFlash('warning', 'Les rendez-vous ne peuvent pas être pris le samedi ou le dimanche.');
                return $this->render('patient/demande.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Vérifier si la date est disponible
            $planning = $planningRepository->findOneBy(['medecin' => $medecinSelectionne]);
            $datesNonDisponibles = $planning ? $planning->getDatesNonDisponibles() : [];

            if (in_array($dateSelectionnee->format('Y-m-d'), $datesNonDisponibles)) {
                $this->addFlash('error', 'Cette date n\'est pas disponible.');
                return $this->render('patient/demande.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Associer le planning et les autres informations au rendez-vous
            $rendezVous->setPlanning($planning);
            $rendezVous->setMedecin($medecinSelectionne);
            $rendezVous->setService($form->get('service')->getData());

            // Enregistrer en base de données
            $entityManager->persist($rendezVous);
            $entityManager->flush();

            // Rediriger vers la page de sélection de l'heure
            return $this->redirectToRoute('selectionner_heure', ['id' => $rendezVous->getId()]);
        }

        // Afficher le formulaire
        return $this->render('patient/demande.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verifier-disponibilite-date', name: 'verifier_disponibilite_date', methods: ['GET'])]
public function verifierDisponibiliteDate(Request $request, PlanningRepository $planningRepository): Response
{
    $medecinId = $request->query->get('medecinId');
    $dateSelectionnee = $request->query->get('date');

    $planning = $planningRepository->findOneBy(['medecin' => $medecinId]);

    if ($planning && in_array($dateSelectionnee, $planning->getDatesNonDisponibles())) {
        return $this->json(['disponible' => false]);
    }

    return $this->json(['disponible' => true]);
}

#[Route('/selectionner-heure/{id}', name: 'selectionner_heure')]
public function selectionnerHeure(RendezVous $rendezVous, Request $request, PlanningRepository $planningRepository, EntityManagerInterface $entityManager): Response
{
    $medecin = $rendezVous->getMedecin();
    $date = $rendezVous->getDate()->format('Y-m-d');

    $planning = $planningRepository->findOneBy([
        'medecin' => $medecin,
        'dateDisponible' => new \DateTime($date),
    ]);

    $initialTimes = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
    $reservedTimes = $planning ? $planning->getTempsReserver() : [];
    $availableTimes = array_diff($initialTimes, $reservedTimes);

    $form = $this->createForm(HeureRendezVousType::class, null, [
        'heuresDisponibles' => $availableTimes,
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $heureSelectionnee = $form->get('heure_selectionnee')->getData();
        $rendezVous->setHeure($heureSelectionnee);

        $entityManager->persist($rendezVous);
        $entityManager->flush();

        return $this->redirectToRoute('valider_heure', ['id' => $rendezVous->getId()]);
    }

    return $this->render('patient/select_heure.html.twig', [
        'form' => $form->createView(),
        'rendezVous' => $rendezVous,
    ]);
}

#[Route('/valider-heure/{id}', name: 'valider_heure')]
public function validerHeure(Request $request, RendezVous $rendezVous, PlanningRepository $planningRepository, EntityManagerInterface $entityManager): Response
{
    $heureSelectionnee = $request->request->get('heure_selectionnee');

    if (!$heureSelectionnee) {
        $this->addFlash('error', 'Veuillez sélectionner une heure.');
        return $this->redirectToRoute('ListRendezVous', ['id' => $rendezVous->getId()]);
    }

    $rendezVous->setHeure($heureSelectionnee);
    $entityManager->persist($rendezVous);

    $medecin = $rendezVous->getMedecin();
    $date = $rendezVous->getDate();
    $planning = $planningRepository->findOneBy([
        'medecin' => $medecin,
        'dateDisponible' => $date,
    ]);

    if (!$planning) {
        $planning = new Planning();
        $planning->setMedecin($medecin);
        $planning->setDateDisponible($date);
    }

    $planning->ajouterTempsReserver($heureSelectionnee);
    $entityManager->persist($planning);

    $entityManager->flush();

    $this->addFlash('success', 'Rendez-vous enregistré avec succès.');

    return $this->redirectToRoute('gere_rendezvous');
}

#[Route('/gere-rendezvous', name: 'gere_rendezvous')] 
public function gereRendezVous(Request $request, RendezVousRepository $rendezVousRepository, Security $security): Response
{
    $user = $security->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException("Vous devez être connecté pour voir vos rendez-vous.");
    }

    $statut = $request->query->get('statut');
    $medecinNom = $request->query->get('medecinNom');
    
    // Utilisation des filtres personnalisés
    $rendezVousList = $rendezVousRepository->findByFilters($statut, $medecinNom);

    return $this->render('medecin/GereRendezVous.html.twig', [
        'rendezVousList' => $rendezVousList,
        'filtreStatut' => $statut,
        'filtreMedecinNom' => $medecinNom
    ]);
}
    #[Route('/ListRdv', name: 'ListRendezVous')]
public function ListeRendezVous(RendezVousRepository $rendezVousRepository, Security $security): Response
{
    // Récupérer l'utilisateur connecté
    $user = $security->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException("Vous devez être connecté pour voir vos rendez-vous.");
    }

    // Récupérer les rendez-vous de l'utilisateur connecté
    $rendezVousList = $rendezVousRepository->findBy(['patient' => $user]);

    return $this->render('patient/ListeRendezVous.html.twig', [
        'rendezVousList' => $rendezVousList, 
    ]);
}

    

    #[Route('/supprimer-rendezvous/{id}', name: 'supprimer_rendezvous', methods: ['POST'])]
    public function supprimerRendezVous(int $id, RendezVousRepository $rendezVousRepository): Response
    {
        // Récupérer le rendez-vous à supprimer
        $rendezVous = $rendezVousRepository->find($id);

        if ($rendezVous) {
            // Supprimer le rendez-vous
            $this->entityManager->remove($rendezVous);
            $this->entityManager->flush();
            $this->addFlash('success', 'Rendez-vous supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
        }

        return $this->redirectToRoute('gere_rendezvous');
    }
    #[Route('/supprimerRdv/{id}', name: 'supprimerRdv', methods: ['POST'])]
    public function supprimerRdv(int $id, RendezVousRepository $rendezVousRepository): Response
    {
        // Récupérer le rendez-vous à supprimer
        $rendezVous = $rendezVousRepository->find($id);

        if ($rendezVous) {
            // Supprimer le rendez-vous
            $this->entityManager->remove($rendezVous);
            $this->entityManager->flush();
            $this->addFlash('success', 'Rendez-vous supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
        }

        return $this->redirectToRoute('ListRendezVous');
    }

    #[Route('/modifier-rendezvous/{id}', name: 'modifier_rendezvous', methods: ['GET', 'POST'])]
    public function modifierRendezVous(Request $request, RendezVous $rendezVous, ServiceRepository $serviceRepository, EntityManagerInterface $entityManager): Response
    {
        $services = $serviceRepository->findAll();

        $form = $this->createForm(MedecinRendezVousType::class, $rendezVous, [
            'services' => $services,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-vous modifié avec succès !');
            return $this->redirectToRoute('gere_rendezvous');
        }

        return $this->render('medecin/modifierRendezVous.html.twig', [
            'form' => $form->createView(),
            'rendezVous' => $rendezVous,
        ]);
    }
    #[Route('/modifierRDV/{id}', name: 'modifierRDV', methods: ['GET', 'POST'])]
    public function modifierRdv(Request $request, RendezVous $rendezVous, ServiceRepository $serviceRepository, EntityManagerInterface $entityManager): Response
    {
        $services = $serviceRepository->findAll();

        $form = $this->createForm(RendezVousModifierType::class, $rendezVous, [
            'services' => $services,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-vous modifié avec succès !');
            return $this->redirectToRoute('gere_rendezvous');
        }

        return $this->render('patient/modifier_rdv.html.twig', [
            'form' => $form->createView(),
            'rendezVous' => $rendezVous,
        ]);
    }

   





}
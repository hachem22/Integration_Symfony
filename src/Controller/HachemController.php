<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Form\MedecinRendezVousType;
use App\Form\HeureRendezVousType;

use App\Entity\Planning;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\ServiceRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\PlanningRepository;
use App\Entity\DossierMedical;
use App\Entity\Visite;
use App\Form\DossierMedicalType;
use App\Form\VisiteType;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Enum\RendezVousStatus;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\RendezVousRepository;
use App\Form\HeureType;

use Symfony\Component\Routing\Annotation\Route;

final class HachemController extends AbstractController{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/gere-Rdv', name: 'gereRdv')]
    public function gereRdv(RendezVousRepository $rendezVousRepository): Response
    {
        // Récupérer tous les rendez-vous
        $rendezVousList = $rendezVousRepository->findAll();

        return $this->render('utilisateur/dashboardHachem/dashboardRdv.html.twig', [
            'rendezVousList' => $rendezVousList, // Passer les rendez-vous à la vue
        ]);
    }
    #[Route('/modifierRdv/{id}', name: 'editRdv', methods: ['GET', 'POST'])]
    public function editRendezVous(Request $request, RendezVous $rendezVous, ServiceRepository $serviceRepository, EntityManagerInterface $entityManager): Response
    {
        $services = $serviceRepository->findAll();

        $form = $this->createForm(MedecinRendezVousType::class, $rendezVous, [
            'services' => $services,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-vous modifié avec succès !');
            return $this->redirectToRoute('gereRdv');
        }

        return $this->render('utilisateur/dashboardHachem/modifier.html.twig', [
            'form' => $form->createView(),
            'rendezVous' => $rendezVous,
        ]);
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

        return $this->redirectToRoute('gereRdv');
    }
    #[Route('/Rdv/accepter/{id}', name: 'accepterRdv', methods: ['POST'])]
    public function accepterRdv(int $id, RendezVousRepository $rendezVousRepository, EntityManagerInterface $entityManager, PlanningRepository $planningRepository): Response
    {
        $rendezVous = $rendezVousRepository->find($id);

        if (!$rendezVous) {
            throw $this->createNotFoundException('Le rendez-vous n\'existe pas.');
        }

        // Récupérer les informations du rendez-vous
        $medecin = $rendezVous->getMedecin();
        $date = $rendezVous->getDate();
        $heure = $rendezVous->getHeure();

        // Mettre à jour l'état du rendez-vous
        $rendezVous->setRendezVousStatus('Confirme');

        // Mettre à jour l'entité Planning
        $planning = $planningRepository->findOneBy([
            'medecin' => $medecin,
            'dateDisponible' => $date,
        ]);

        if ($planning) {
            $planning->ajouterTempsReserver($heure);
            $entityManager->persist($planning);
        }

        $entityManager->persist($rendezVous);
        $entityManager->flush();

        return $this->redirectToRoute('gereRdv');
    }

    #[Route('/Rdv/refuser/{id}', name: 'refuserRdv', methods: ['POST'])]
    public function refuserRdv(int $id, RendezVousRepository $rendezVousRepository, EntityManagerInterface $entityManager): Response
    {
        $rendezVous = $rendezVousRepository->find($id);

        if (!$rendezVous) {
            throw $this->createNotFoundException('Le rendez-vous n\'existe pas.');
        }

        // Mettre à jour l'état du rendez-vous
        $rendezVous->setRendezVousStatus('Annule');
        $entityManager->persist($rendezVous);
        $entityManager->flush();

        return $this->redirectToRoute('gereRdv');
    }



    #[Route('Mon_Dos/', name: 'Mon_dos')]
    public function indexxx(DossierMedicalRepository $dossierMedicalRepository): Response
    {
        // Récupérer tous les dossiers médicaux
        $dossier_medicals = $dossierMedicalRepository->findAll();

        return $this->render('utilisateur/dashboardHachem/dos.html.twig', [
            'dossier_medicals' => $dossier_medicals,
        ]);
    }

    #[Route('/newDos', name: 'newDos', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dossierMedical = new DossierMedical();
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            $this->addFlash('success', 'Dossier médical créé avec succès !');
            return $this->redirectToRoute('Mon_dos');
        }

        return $this->render('utilisateur/dashboardHachem/ajoutDos.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/edit/{id}', name: 'editDossier', methods: ['GET', 'POST'])]
public function editDossier(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
{
    // Create the form for editing the DossierMedical
    $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
    $form->handleRequest($request);

    // Handle form submission
    if ($form->isSubmitted() && $form->isValid()) {
        // Save the updated DossierMedical to the database
        $entityManager->flush();

        // Add a success flash message
        $this->addFlash('success', 'Dossier médical mis à jour avec succès !');

        // Redirect to the index page or another appropriate route
        return $this->redirectToRoute('Mon_dos');
    }

    // Render the edit template with the form
    return $this->render('utilisateur/dashboardHachem/ModifierDos.html.twig', [
        'form' => $form->createView(),
        'dossierMedical' => $dossierMedical, // Pass the DossierMedical object to the template
    ]);
}
#[Route('/dos/{id}/visites', name: 'indexVS', methods: ['GET'])]
public function indext(DossierMedical $dossierMedical): Response
{
// Vous récupérez toutes les visites liées à ce dossier médical.
$visites = $dossierMedical->getVisites();

return $this->render('utilisateur/dashboardHachem/visitesDos.html.twig', [
    'dossierMedical' => $dossierMedical,  // Vous passez l'objet dossierMedical à la vue
    'visites' => $visites,  // Vous passez les visites à la vue
]);
}

#[Route('/demandeRdv', name: 'demandeRdv')]
public function demandeRendezVous(
    Request $request,
    ServiceRepository $serviceRepository,
    UtilisateurRepository $utilisateurRepository,
    PlanningRepository $planningRepository,
    EntityManagerInterface $entityManager
): Response {
    // Récupérer tous les services
    $services = $serviceRepository->findAll();

    if (empty($services)) {
        $this->addFlash('warning', 'Aucun service disponible pour le moment.');
        return $this->render('utilisateur/dashboardHachem/demandeRdv.html.twig', [
            'form' => null,
        ]);
    }

    // Créer une nouvelle instance de RendezVous
    $rendezVous = new RendezVous();
    $rendezVous->setRendezVousStatus('EnAttente');

    // Récupérer les dates disponibles
    $datesDisponibles = [];
    foreach ($planningRepository->findAll() as $planning) {
        $datesDisponibles[] = $planning->getDateDisponible();
    }

    // Créer le formulaire
    $form = $this->createForm(RendezVousType::class, $rendezVous, [
        'services' => $services,
        'datesDisponibles' => $datesDisponibles,
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer la date sélectionnée
        $dateSelectionnee = $form->get('date')->getData();

        // Vérifier si une date a été sélectionnée
        if (!$dateSelectionnee) {
            $this->addFlash('error', 'Veuillez sélectionner une date.');
            return $this->render('utilisateur/dashboardHachem/demandeRdv.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Trouver le Planning correspondant à la date sélectionnée
        $planning = $planningRepository->findOneBy(['dateDisponible' => $dateSelectionnee]);

        if (!$planning) {
            $this->addFlash('error', 'Aucun planning trouvé pour la date sélectionnée.');
            return $this->render('utilisateur/dashboardHachem/demandeRdv.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Associer le Planning au RendezVous
        $rendezVous->setPlanning($planning);
        // Définir les autres propriétés
        $rendezVous->setMedecin($form->get('medecin')->getData());
        $rendezVous->setService($form->get('service')->getData());

        // Enregistrer le rendez-vous en base de données (sans l'heure pour l'instant)
        $entityManager->persist($rendezVous);
        $entityManager->flush();

        // Rediriger vers la page de sélection de l'heure
        return $this->redirectToRoute('selectionner_heure', ['id' => $rendezVous->getId()]);
    }

    // Afficher le formulaire
    return $this->render('utilisateur/dashboardHachem/demandeRdv.html.twig', [
        'form' => $form->createView(),
    ]);
}
}

<?php

namespace App\Controller;

use App\Entity\Lit;
use App\Entity\Chambre;
use App\Form\LitType;
use App\Form\AffectationLitType;
use App\Repository\LitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Enum\LitStatut;
use App\Service\EntretienService;

final class LitController extends AbstractController
{
    #[Route('/lit', name: 'app_lit')]
    public function index(LitRepository $repository): Response
    {
        return $this->render('lit/index.html.twig', [
            'lits' => $repository->findAll()
        ]);
    }

    #[Route('/lit/nouveau', name: 'lit.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $lit = new Lit();
        $form = $this->createForm(LitType::class, $lit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $lit->getChambre();

            // Vérifier si la capacité de la chambre est atteinte
            if ($chambre->getLits()->count() >= $chambre->getCapacite()) {
                $this->addFlash('error', 'La capacité maximale de lits pour cette chambre est atteinte.');
                return $this->redirectToRoute('lit.new');
            }

            // Associer le lit à la chambre
            $lit->setChambre($chambre);

            $manager->persist($lit);
            $manager->flush();

            $this->addFlash('success', 'Le lit a été ajouté avec succès.');
            return $this->redirectToRoute('app_lit');
        }

        return $this->render('lit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/litfront/nouveau', name: 'lit.newfront', methods: ['GET', 'POST'])]
    public function newfront(Request $request, EntityManagerInterface $manager): Response
    {
        $lit = new Lit();
        $form = $this->createForm(LitType::class, $lit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $lit->getChambre();

            // Vérifier si la capacité de la chambre est atteinte
            if ($chambre->getLits()->count() >= $chambre->getCapacite()) {
                $this->addFlash('error', 'La capacité maximale de lits pour cette chambre est atteinte.');
                return $this->redirectToRoute('lit.newfront');
            }

            // Associer le lit à la chambre
            $lit->setChambre($chambre);

            $manager->persist($lit);
            $manager->flush();

            $this->addFlash('success', 'Le lit a été ajouté avec succès.');
            return $this->redirectToRoute('app_litfront');
        }

        return $this->render('lit/frontoffice/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/lit/edition/{id}', name: 'lit.edit', methods: ['GET', 'POST'])]
    public function edit(Lit $lit, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(LitType::class, $lit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $lit->getChambre();

            // Vérifier si la capacité de la chambre est atteinte
            if ($chambre->getLits()->count() > $chambre->getCapacite()) {
                $this->addFlash('error', 'La capacité maximale de lits pour cette chambre est atteinte.');
                return $this->redirectToRoute('lit.edit', ['id' => $lit->getId()]);
            }

            $manager->flush();

            $this->addFlash('success', 'Le lit a été modifié avec succès.');
            return $this->redirectToRoute('app_lit');
        }

        return $this->render('lit/edit.html.twig', [
            'form' => $form->createView(),
            'lit' => $lit,
        ]);
    }
    #[Route('/lit/editionfront/{id}', name: 'lit.editfront', methods: ['GET', 'POST'])]
    public function editfront(Lit $lits, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(LitType::class, $lits);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $lits->getChambre();

            // Vérifier si la capacité de la chambre est atteinte
            if ($chambre->getLits()->count() > $chambre->getCapacite()) {
                $this->addFlash('error', 'La capacité maximale de lits pour cette chambre est atteinte.');
                return $this->redirectToRoute('lit.editfront', ['id' => $lits->getId()]);
            }

            $manager->flush();

            $this->addFlash('success', 'Le lit a été modifié avec succès.');
            return $this->redirectToRoute('app_litfront');
        }

        return $this->render('lit/frontoffice/index.html.twig', [
            'form' => $form->createView(),
            'lit' => $lits,
        ]);
    }

    #[Route('/lit/suppression/{id}', name: 'lit.delete', methods: ['GET'])]
    public function delete(Lit $lit, EntityManagerInterface $manager): Response
    {
        if (!$lit) {
            $this->addFlash('error', 'Le lit n\'a pas été trouvé.');
            return $this->redirectToRoute('app_lit');
        }

        // Supprimer le lit de la chambre associée
        $chambre = $lit->getChambre();
        $chambre->removeLit($lit);

        $manager->remove($lit);
        $manager->flush();

        $this->addFlash('success', 'Le lit a été supprimé avec succès.');
        return $this->redirectToRoute('app_lit');
    }
    #[Route('/lit/suppressionfront/{id}', name: 'lit.deletefront', methods: ['GET'])]
    public function deletefront(Lit $lit, EntityManagerInterface $manager): Response
    {
        if (!$lit) {
            $this->addFlash('error', 'Le lit n\'a pas été trouvé.');
            return $this->redirectToRoute('app_litfront');
        }

        // Supprimer le lit de la chambre associée
        $chambre = $lit->getChambre();
        $chambre->removeLit($lit);

        $manager->remove($lit);
        $manager->flush();

        $this->addFlash('success', 'Le lit a été supprimé avec succès.');
        return $this->redirectToRoute('app_litfront');
    }

    #[Route('/lit/{id}/chambre', name: 'lit.chambre', methods: ['GET'])]
    public function showChambre(Lit $lit): Response
    {
        $chambre = $lit->getChambre();

        return $this->render('lit/index.html.twig', [
            'lit' => $lit,
            'chambre' => $chambre,
        ]);
    }
    #[Route('/lit/affectation/{id}', name: 'lit.affecter', methods: ['GET', 'POST'])]
public function affecterLit(Lit $lit, Request $request, EntityManagerInterface $manager): Response
{
    $form = $this->createForm(AffectationLitType::class, $lit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $patient = $lit->getPatient();

        // Vérifier et mettre à jour le statut du lit
        if ($patient) {
            $lit->setType('occupe');  // 'occupe' comme chaîne de caractères
        } else {
            $lit->setType('libre');   // 'libre' comme chaîne de caractères
        }

        $manager->flush();

        $this->addFlash('success', 'Le lit a été mis à jour avec succès.');
        return $this->redirectToRoute('app_responsable');
    }

    return $this->render('lit/affectation.html.twig', [
        'form' => $form->createView(),
        'lit' => $lit,
    ]);
}

    public function show(LitRepository $repository): Response
{
    return $this->render('lit/index.html.twig', [
        'lits' => $repository->findAll(),
        'typeLitLibre' => 'libre',   // Passer la chaîne 'libre'
        'typeLitOccupe' => 'occupe', // Passer la chaîne 'occupe'
    ]);
}
#[Route('/front/lit', name: 'app_litfront')]
public function indexshow(LitRepository $repository): Response
{
    
    return $this->render('lit/frontoffice/index.html.twig', [
        'lits' => $repository->findAll()
    ]);
}
#[Route('/lit/desaffectation/{id}', name: 'lit.desaffecter', methods: ['GET', 'POST'])]
    public function desaffecterPatient(
        Lit $lit, 
        Request $request, 
        EntityManagerInterface $manager,
        EntretienService $entretienService
    ): Response
    {
        // Vérifier que le lit existe
        if (!$lit) {
            $this->addFlash('error', 'Le lit n\'a pas été trouvé.');
            return $this->redirectToRoute('app_lit');
        }

        // Vérifier si un patient est actuellement affecté à ce lit
        $patient = $lit->getPatient();
        if (!$patient) {
            $this->addFlash('warning', 'Aucun patient n\'est actuellement affecté à ce lit.');
            return $this->redirectToRoute('app_responsable');
        }

        // Demande de confirmation pour la désaffectation (première visite)
        if ($request->isMethod('GET')) {
            return $this->render('lit/desaffectation.html.twig', [
                'lit' => $lit,
                'patient' => $patient
            ]);
        }

        // Traitement de la désaffectation (après confirmation)
        if ($request->isMethod('POST')) {
            // Désaffecter le patient du lit
           
            $lit->setPatient(null);
            $lit->setType('libre');

            // Mettre à jour la base de données
            $manager->persist($patient);
            $manager->persist($lit);
            
            // Créer automatiquement un entretien pour la chambre
            $entretienService->creerEntretienApresDepartPatient($lit);
            
            // Mettre à jour le statut de la chambre
            $chambre = $lit->getChambre();
            if ($chambre) {
                $chambre->updateStatus();
                $manager->persist($chambre);
            }
            
            $manager->flush();

            $this->addFlash('success', 'Le patient a été désaffecté et un entretien de chambre a été planifié automatiquement.');
            return $this->redirectToRoute('app_responsable');
        }

        // Redirection par défaut
        return $this->redirectToRoute('app_responsable');
    }




}
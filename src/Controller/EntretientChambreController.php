<?php

namespace App\Controller;

use App\Entity\EntretientChambre;
use App\Form\EntretientChambreType;
use App\Repository\EntretientChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\StatutEntretientChambre;

class EntretientChambreController extends AbstractController
{
    #[Route('/entretient_chambre', name: 'app_entretient_chambre')]
    public function index(EntretientChambreRepository $repository,EntityManagerInterface $manager): Response
    {
        $this->updateExpiredEntretients($repository, $manager);
        return $this->render('entretient_chambre/index.html.twig', [
            'entretients' => $repository->findAll(),
        ]);
    }
    #[Route('/entretient_chambrefront', name: 'app_entretient_chambrefront')]
    public function indexfront(EntretientChambreRepository $repository,EntityManagerInterface $manager): Response
    {
       
        $this->updateExpiredEntretients($repository, $manager);
        return $this->render('entretient_chambre/front/index.html.twig', [
            'entretients' => $repository->findAll(),
        ]);
    }

    #[Route('/entretient_chambre/nouveau', name: 'entretient_chambre.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $entretient = new EntretientChambre();
        $form = $this->createForm(EntretientChambreType::class, $entretient);
        $form->handleRequest($request);
   
        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $entretient->getChambre();
           
            if ($chambre) {
                // Mise à jour du statut de la chambre à "occupee"
                $chambre->setActive('Maintenance');
                $manager->persist($chambre);
            }
   
            $manager->persist($entretient);
            $manager->flush();
   
            $this->addFlash('success', 'Entretien ajouté et chambre mise en "occupée".');
            return $this->redirectToRoute('app_entretient_chambre');
        }
   
        return $this->render('entretient_chambre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/entretient_chambrefront/nouveau', name: 'entretient_chambrefront.new', methods: ['GET', 'POST'])]
    public function newfront(Request $request, EntityManagerInterface $manager): Response
    {
        $entretient = new EntretientChambre();
        $form = $this->createForm(EntretientChambreType::class, $entretient);
        $form->handleRequest($request);
   
        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $entretient->getChambre();
            if (empty($entretient->getDetails())) {
                $entretient->setDetails($entretient->autoCompleteDescription());}
           
            if ($chambre) {
                // Mise à jour du statut de la chambre à "occupee"
                $chambre->setActive('Maintenance');
                $manager->persist($chambre);
            }
   
            $manager->persist($entretient);
            $manager->flush();
   
            $this->addFlash('success', 'Entretien ajouté et chambre mise en "occupée".');
            return $this->redirectToRoute('app_entretient_chambrefront');
        }
   
        return $this->render('entretient_chambre/front/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
   

    #[Route('/entretient_chambre/edition/{id}', name: 'entretient_chambre.edit', methods: ['GET', 'POST'])]
    public function edit(EntretientChambre $entretient, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(EntretientChambreType::class, $entretient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'Entretien modifié avec succès.');
            return $this->redirectToRoute('app_entretient_chambre');
        }

        return $this->render('entretient_chambre/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/entretient_chambrefront/edition/{id}', name: 'entretient_chambrefront.edit', methods: ['GET', 'POST'])]
    public function editfront(EntretientChambre $entretient, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(EntretientChambreType::class, $entretient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'Entretien modifié avec succès.');
            return $this->redirectToRoute('app_entretient_chambrefront');
        }

        return $this->render('entretient_chambre/front/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/entretient_chambre/suppression/{id}', name: 'entretient_chambre.delete', methods: ['GET'])]
    public function delete(EntretientChambre $entretient, EntityManagerInterface $manager): Response
    {
        if (!$entretient) {
            $this->addFlash('danger', 'Entretien introuvable.');
            return $this->redirectToRoute('app_entretient_chambre');
        }

        $manager->remove($entretient);
        $manager->flush();

        $this->addFlash('success', 'Entretien supprimé avec succès.');
        return $this->redirectToRoute('app_entretient_chambre');
    }
    #[Route('/entretient_chambrefront/suppression/{id}', name: 'entretient_chambrefront.delete', methods: ['GET'])]
    public function deletefront(EntretientChambre $entretient, EntityManagerInterface $manager): Response
    {
        if (!$entretient) {
            $this->addFlash('danger', 'Entretien introuvable.');
            return $this->redirectToRoute('app_entretient_chambrefront');
        }

        $manager->remove($entretient);
        $manager->flush();

        $this->addFlash('success', 'Entretien supprimé avec succès.');
        return $this->redirectToRoute('app_entretient_chambrefront');
    }
    private function updateExpiredEntretients(EntretientChambreRepository $repository, EntityManagerInterface $manager): void
    {
       // Création d'un DateTime avec la date ET l'heure actuelles
    $now = new \DateTime('now');
    $expiredEntretients = $repository->findExpiredEntretients($now);
   
    $updated = 0;
    foreach ($expiredEntretients as $entretient) {
        // Vérifier si le statut est différent de TERMINE
        if ($entretient->getStatut() !== StatutEntretientChambre::termine) {
            $entretient->setStatut(StatutEntretientChambre::termine);
           
            // Si une chambre est associée, la remettre en état "disponible"
            $chambre = $entretient->getChambre();
            if ($chambre && $chambre->getActive() === 'Maintenance') {
                $chambre->setActive('Disponible');
            }
           
            $manager->persist($entretient);
            $updated++;
        }
    }
   
    if ($updated > 0) {
        $manager->flush();
        $this->addFlash('info', "$updated entretien(s) automatiquement marqué(s) comme terminé(s).");
    }
}
}

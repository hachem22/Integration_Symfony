<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\StockPharmacie;
use App\Form\CommandeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\StockPharmacieRepository;

class CommandeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'app_commande_index')]
    public function index(EntityManagerInterface $entityManager, StockPharmacieRepository $stockPharmacieRepository): Response
    {
        $stock_pharmacies = $entityManager->getRepository(StockPharmacie::class)->findAll();
        $commandes = $entityManager->getRepository(Commande::class)->findAll();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'stock_pharmacies' => $stock_pharmacies
        ]);
    }

    #[Route('/commande/livraisons', name: 'app_commande_livraisons')]
    public function livraisons(): Response
    {
        $commandes = $this->entityManager->getRepository(Commande::class)->findBy(
            [],
            ['dateCommande' => 'DESC']
        );

        return $this->render('stock_pharmacie/livraisons.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/commande/new', name: 'app_commande_new')]
    public function new(Request $request): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($commande);
            $this->entityManager->flush();

            $this->addFlash('success', 'Commande créée avec succès');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/commande/new/{id}', name: 'app_commande_new_with_product')]
    public function newWithProduct(Request $request, StockPharmacie $stockPharmacie, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $commande->setStockPharmacie($stockPharmacie);
        $commande->setDateCommande(new \DateTime());
        
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            $this->addFlash('success', 'La commande a été créée avec succès.');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form->createView(),
            'stock_pharmacie' => $stockPharmacie
        ]);
    }

    #[Route('/commande/{id}/show', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        $stockPharmacie = $commande->getStockPharmacie();
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'stock_pharmacie' => $stockPharmacie
        ]);
    }

    #[Route('/commande/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande): Response
    {
        if ($commande->isLivree()) {
            $this->addFlash('error', 'Une commande livrée ne peut pas être modifiée.');
            return $this->redirectToRoute('app_commande_index');
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'La commande a été modifiée avec succès.');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form->createView()
        ]);
    }

    #[Route('/commande/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande): Response
    {
        if ($commande->isLivree()) {
            $this->addFlash('error', 'Une commande livrée ne peut pas être supprimée.');
            return $this->redirectToRoute('app_commande_index');
        }

        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($commande);
            $this->entityManager->flush();
            $this->addFlash('success', 'La commande a été supprimée.');
        }

        return $this->redirectToRoute('app_commande_index');
    }

    #[Route('/commande/{id}/confirmer-livraison', name: 'app_commande_confirmer_livraison', methods: ['POST'])]
    public function confirmerLivraison(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('confirmer-livraison'.$commande->getId(), $request->request->get('_token'))) {
            $commande->setStatut('Livrée');
            $this->entityManager->flush();
            $this->addFlash('success', 'La commande a été confirmée avec succès.');
        }

        return $this->redirectToRoute('app_commande_livraisons');
    }
} 
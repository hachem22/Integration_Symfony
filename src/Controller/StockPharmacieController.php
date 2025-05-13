<?php

namespace App\Controller;

use App\Entity\StockPharmacie;
use App\Entity\Panier;
use Knp\Snappy\Pdf;
use App\Form\StockPharmacieType;
use App\Repository\StockPharmacieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\EvenementRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\ReclamationRepository;

#[Route('/stock/pharmacie')]
class StockPharmacieController extends AbstractController
{
    private $entityManager;
    private $pdf;

    public function __construct(EntityManagerInterface $entityManager,Pdf $pdf)
    {
        $this->entityManager = $entityManager;
        $this->pdf = $pdf;

    }

    private function generateSafeFilename(string $originalFilename): string
    {
        // Remplacer les caractères spéciaux et les espaces
        $safeFilename = preg_replace('/[^A-Za-z0-9-]/', '-', $originalFilename);
        // Convertir en minuscules
        $safeFilename = strtolower($safeFilename);
        // Supprimer les tirets multiples
        $safeFilename = preg_replace('/-+/', '-', $safeFilename);
        // Supprimer les tirets au début et à la fin
        $safeFilename = trim($safeFilename, '-');
        
        return $safeFilename;
    }

    #[Route('/', name: 'app_stock_pharmacie_index', methods: ['GET'])]
    public function index(StockPharmacieRepository $stockPharmacieRepository): Response
    {
        return $this->render('stock_pharmacie/index.html.twig', [
            'stock_pharmacies' => $stockPharmacieRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'app_stock_pharmacie_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $stockPharmacie = new StockPharmacie();
        $form = $this->createForm(StockPharmacieType::class, $stockPharmacie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement du fichier si nécessaire
            /** @var UploadedFile $imageFile */
            if ($imageFile = $form->get('image')->getData()) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $stockPharmacie->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                }
            }

            $stockPharmacie->updateStatu();
            $this->entityManager->persist($stockPharmacie);
            $this->entityManager->flush();

            $this->addFlash('success', 'Médicament ajouté avec succès');
            return $this->redirectToRoute('app_stock_pharmacie_index');
        }

        return $this->render('stock_pharmacie/new.html.twig', [
            'stock_pharmacie' => $stockPharmacie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/livraisons', name: 'app_stock_pharmacie_livraisons', methods: ['GET'])]
    public function livraisons(EntityManagerInterface $entityManager): Response
    {
        $commandes = $entityManager->getRepository(Commande::class)->findBy([], ['dateCommande' => 'DESC']);
        
        return $this->render('stock_pharmacie/livraisons.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/{id}/livraison/confirmer', name: 'app_stock_pharmacie_livraison_confirmer', methods: ['POST'])]
    public function confirmerLivraison(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('livraison'.$commande->getId(), $request->request->get('_token'))) {
            if ($commande->getStatut() === Commande::STATUT_EN_ATTENTE) {
                // Mise à jour du stock
                $stockPharmacie = $commande->getStockPharmacie();
                $stockPharmacie->setQuantite($stockPharmacie->getQuantite() + $commande->getQuantite());
                $stockPharmacie->updateStatu();
                
                // Mise à jour du statut de la commande
                $commande->setStatut(Commande::STATUT_LIVREE);
                
                $this->entityManager->flush();
                
                $this->addFlash('success', 'La livraison a été confirmée avec succès.');
            } else {
                $this->addFlash('error', 'Cette commande ne peut pas être livrée car elle n\'est pas en attente.');
            }
        }

        return $this->redirectToRoute('app_stock_pharmacie_livraisons');
    }

    #[Route('/livraison/{id}/annuler', name: 'app_stock_pharmacie_livraison_annuler', methods: ['POST'])]
    public function annulerLivraison(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('annulation'.$commande->getId(), $request->request->get('_token'))) {
            if ($commande->getStatut() === Commande::STATUT_EN_ATTENTE) {
                $commande->setStatut(Commande::STATUT_ANNULEE);
                $this->entityManager->flush();
                $this->addFlash('success', 'La commande a été annulée.');
            }
        }
        
        return $this->redirectToRoute('app_stock_pharmacie_livraisons');
    }

    #[Route('/commande/produit/{id}', name: 'app_stock_pharmacie_commander', methods: ['GET', 'POST'])]
    public function commander(Request $request, StockPharmacie $stockPharmacie): Response
    {
        return $this->redirectToRoute('app_commande_new_with_product', ['id' => $stockPharmacie->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_stock_pharmacie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StockPharmacie $stockPharmacie): Response
    {
        $form = $this->createForm(StockPharmacieType::class, $stockPharmacie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement du fichier si nécessaire
            /** @var UploadedFile $imageFile */
            if ($imageFile = $form->get('image')->getData()) {
                // Supprimer l'ancienne image si elle existe
                if ($stockPharmacie->getImage()) {
                    $oldImagePath = $this->getParameter('images_directory').'/'.$stockPharmacie->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $stockPharmacie->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                }
            }

            $stockPharmacie->updateStatu();
            $this->entityManager->flush();

            $this->addFlash('success', 'Médicament modifié avec succès');
            return $this->redirectToRoute('app_stock_pharmacie_index');
        }

        return $this->render('stock_pharmacie/edit.html.twig', [
            'stock_pharmacie' => $stockPharmacie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_stock_pharmacie_delete', methods: ['POST'])]
    public function delete(Request $request, StockPharmacie $stockPharmacie): Response
    {
        if ($this->isCsrfTokenValid('delete' . $stockPharmacie->getId(), $request->request->get('_token'))) {
            try {
                $nomProduit = $stockPharmacie->getNom();
                // Au lieu de supprimer, on désactive
                $stockPharmacie->setActif(false);
                $this->entityManager->flush();
                
                $this->addFlash('success', "Le produit {$nomProduit} a été désactivé.");
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la désactivation du médicament.');
            }
        }

        return $this->redirectToRoute('app_stock_pharmacie_index');
    }

    #[Route('/stock/pharmacie/{id}', name: 'app_stock_pharmacie_show', methods: ['GET'])]
    public function show(StockPharmacie $stockPharmacie): Response
    {
        return $this->render('stock_pharmacie/show.html.twig', [
            'stock_pharmacie' => $stockPharmacie
        ]);
    }

   #[Route('/dashboard-test', name: 'app_test_dashboard')]
public function testDashboard(
    EvenementRepository $evenementRepository,
    StockPharmacieRepository $stockPharmacieRepository,
    ReclamationRepository $reclamationRepository
): Response {
    $stock_pharmacies = $stockPharmacieRepository->findAll();
    $evenements = $evenementRepository->findAll();
    
    $stock_total = count($stock_pharmacies);
    $stock_disponible = 0;
    $stock_rupture = 0;
    $stock_alerte = 0;

    foreach ($stock_pharmacies as $stock) {
        if ($stock->getStatu()) {
            $stock_disponible++;
            if ($stock->getQuantite() < 10) {
                $stock_alerte++;
            }
        } else {
            $stock_rupture++;
        }
    }

    $now = new \DateTime();
    $oneHourLater = (new \DateTime())->modify('+1 hour');

    $prochainesLivraisons = $this->entityManager->getRepository(Commande::class)->createQueryBuilder('c')
        ->where('c.dateLivraison BETWEEN :now AND :oneHourLater')
        ->andWhere('c.statut = :statut')
        ->setParameter('now', $now)
        ->setParameter('oneHourLater', $oneHourLater)
        ->setParameter('statut', 'En attente')
        ->getQuery()
        ->getResult();

    $notifications = [];
    foreach ($prochainesLivraisons as $livraison) {
        $notifications[] = [
            'type' => 'livraison',
            'message' => sprintf(
                'Livraison prévue pour la commande #%d (%s) dans moins d\'une heure',
                $livraison->getId(),
                $livraison->getStockPharmacie()->getNom()
            ),
            'date' => $livraison->getDateLivraison(),
            'id' => $livraison->getId()
        ];
    }

    // ✅ Récupérer l'utilisateur connecté
    $user = $this->getUser();

    // ✅ Filtrer les réclamations en attente de cet utilisateur uniquement
    $reclamations_en_attente = $reclamationRepository->count([
        'statut' => 'EN_ATTENTE',
        'utilisateur' => $user
    ]);

    return $this->render('pharmacien/dashboard.html.twig', [
        'stock_pharmacies' => $stock_pharmacies,
        'evenements' => $evenements,
        'stock_total' => $stock_total,
        'stock_disponible' => $stock_disponible,
        'stock_rupture' => $stock_rupture,
        'stock_alerte' => $stock_alerte,
        'notifications' => $notifications,
        'reclamations_en_attente' => $reclamations_en_attente,
    ]);
}



    #[Route('/stock/alert/pdf', name: 'app_stock_alert_pdf')]
    public function generatePdf(): Response
    {
        // Fetch products with "critique" status (quantity < 10)
        $stock_pharmacies = $this->entityManager
            ->getRepository(StockPharmacie::class)
            ->createQueryBuilder('s')
            ->where('s.quantite < :critiqueThreshold')
            ->setParameter('critiqueThreshold', 10)
            ->getQuery()
            ->getResult();
    
        // Render the PDF template
        $html = $this->renderView('stock_pharmacie/pdf_stock_alert.html.twig', [
            'stock_pharmacies' => $stock_pharmacies,
        ]);
    
        // Generate the PDF
        $filename = 'stock_alerte_critique.pdf';
        return new Response(
            $this->pdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
    #[Route('/panier/ajouter/{id}', name: 'app_panier_ajouter', methods: ['POST'])]
public function ajouterAuPanier(Request $request, StockPharmacie $produit): Response
{
    // Get the current user (assuming you're using Symfony's security system)
    $user = $this->getUser();

    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour ajouter un produit au panier.');
        return $this->redirectToRoute('app_login');
    }

    // Check if the product is already in the cart
    $panier = $this->entityManager->getRepository(Panier::class)->findOneBy([
        'utilisateur' => $user,
        'produit' => $produit,
    ]);

    if ($panier) {
        // If the product is already in the cart, increase the quantity
        $panier->setQuantite($panier->getQuantite() + 1);
    } else {
        // If the product is not in the cart, create a new cart item
        $panier = new Panier();
        $panier->setUtilisateur($user);
        $panier->setProduit($produit);
        $panier->setQuantite(1);
    }

    // Save the cart item
    $this->entityManager->persist($panier);
    $this->entityManager->flush();

    $this->addFlash('success', 'Le produit a été ajouté au panier.');
    return $this->redirectToRoute('app_catalogue_index');
}
}

<?php
namespace App\Controller;

use App\Entity\Panier;
use App\Entity\StockPharmacie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/panier', name: 'app_panier')]
    public function index(): Response
    {
        // Get the current user
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir votre panier.');
            return $this->redirectToRoute('app_login');
        }

        // Fetch the cart items for the current user
        $panierItems = $this->entityManager->getRepository(Panier::class)->findBy(['utilisateur' => $user]);

        return $this->render('panier/index.html.twig', [
            'panierItems' => $panierItems,
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_panier_ajouter', methods: ['POST'])]
    public function ajouterAuPanier(Request $request, StockPharmacie $produit): Response
    {
        // Get the current user
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
        return $this->redirectToRoute('app_stock_pharmacie_index');
    }

    #[Route('/panier/supprimer/{id}', name: 'app_panier_supprimer', methods: ['POST'])]
    public function supprimerDuPanier(Request $request, Panier $panier): Response
    {
        // Check if the current user owns the cart item
        if ($panier->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cet article.');
        }

        // Remove the cart item
        $this->entityManager->remove($panier);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le produit a été supprimé du panier.');
        return $this->redirectToRoute('app_panier');
    }
}
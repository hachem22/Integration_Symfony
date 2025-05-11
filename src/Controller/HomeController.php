<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\StockPharmacie;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/commander/{id}', name: 'commander_produit')]
    public function commander(StockPharmacie $stockPharmacie): Response
    {
        return $this->redirectToRoute('app_commande_new_with_product', ['id' => $stockPharmacie->getId()]);
    }
} 

<?php

namespace App\Controller;

use App\Repository\StockPharmacieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CatalogueController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalogue_index')]
    public function index(StockPharmacieRepository $stockPharmacieRepository): Response
    {
        return $this->render('catalogue/index.html.twig', [
            'medicaments' => $stockPharmacieRepository->findAll()
        ]);
    }
} 
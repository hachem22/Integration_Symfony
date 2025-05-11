<?php

namespace App\Controller;

use App\Repository\StockPharmacieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PharmacologieController extends AbstractController
{
    #[Route('/pharmacologie', name: 'app_pharmacologie')]
    public function index(StockPharmacieRepository $stockPharmacieRepository): Response
    {
        return $this->render('pharmacologie/index.html.twig', [
            'medicaments' => $stockPharmacieRepository->findAll()
        ]);
    }
} 
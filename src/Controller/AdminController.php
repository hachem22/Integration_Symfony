<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'base_admin')]
    public function admin(): Response
    {
        return $this->render('admin/base_admin.html.twig');
    }
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(): Response
    {
        // Supposons que vous ayez un service ou un repository pour récupérer ces données
        $likesDislikesData = [
            'totalLikes' => 120, // Exemple de données
            'totalDislikes' => 30
        ];

        return $this->render('admin/dashboard.html.twig', [
            'likesDislikesData' => $likesDislikesData
        ]);
    }

    #[Route('/admin/patients', name: 'admin_patients')]
    public function managePatients(): Response
    {
        return $this->render('admin/manage_patients.html.twig');
    }
    #[Route('/admin/doctors', name: 'admin_doctors')]
    public function manageDoctors(): Response
    {
        return $this->render('admin/manage_doctors.html.twig');
    }
}
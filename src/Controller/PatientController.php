<?php
// src/Controller/PatientController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\PatientForm;
use App\Enum\UtilisateurRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatientController extends AbstractController
{
    #[Route('/patient/new', name: 'patient_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Utilisateur();

        // Set the default role to 'Patient'
        $patient->setUtilisateurRole(UtilisateurRole::Patient);

        $form = $this->createForm(PatientForm::class, $patient);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new patient to the database
            $entityManager->persist($patient);
            $entityManager->flush();

            // Redirect to a success page or show a success message
            return $this->redirectToRoute('app_utilisateur_index');
        }

        return $this->render('auth-register-basic.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
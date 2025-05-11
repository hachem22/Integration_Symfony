<?php

namespace App\Controller;

use App\Entity\Visite;
use App\Entity\DossierMedical;
use App\Form\VisiteType;
use App\Repository\DossierMedicalRepository;
use App\Repository\VisiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use App\Repository\UtilisateurRepository;
use App\Entity\Utilisateur;

use Knp\Component\Pager\PaginatorInterface;

#[Route('/visite')]
class VisiteController extends AbstractController
{
   // La route pour afficher les visites d'un dossier médical donné
   
   #[Route('/dossier/medical/{id}/visites', name: 'visite_index', methods: ['GET'])]
   public function index(DossierMedical $dossierMedical): Response
   {
       // Assurez-vous que vous récupérez les données des visites liées à ce dossier médical
       $visites = $dossierMedical->getVisites();  // Par exemple, une méthode pour obtenir les visites d'un dossier médical
       
       // Retourner la réponse en passant le dossier médical à la vue
       return $this->render('dossier_medical/visites.html.twig', [
           'dossierMedical' => $dossierMedical,  // Passer l'objet dossierMedical à Twig
           'visites' => $visites,  // Passer la liste des visites
       ]);
   }
   

   #[Route('/modifier/{id}/{dossierId}', name: 'visite_modifier', methods: ['GET', 'POST'])]
public function modifier(Request $request, Visite $visite, int $dossierId, EntityManagerInterface $entityManager): Response
{
    if ($visite->getDossierMedical()->getId() !== $dossierId) {
        throw $this->createNotFoundException('Cette visite ne fait pas partie de ce dossier médical');
    }

    $form = $this->createForm(VisiteType::class, $visite);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('visite_index', ['dossierId' => $dossierId]);
    }

    return $this->render('visite/modifier.html.twig', [
        'form' => $form->createView(),
        'dossierId' => $dossierId,
        'visite' => $visite, // Add this line to pass the visite variable
    ]);
}
#[Route('/supprimer/{id}/{dossierId}', name: 'visite_supprimer', methods: ['POST'])]
public function supprimer(Request $request, Visite $visite, int $dossierId, EntityManagerInterface $entityManager): Response
{
    // Debug: Check if the visit exists
    if (!$visite) {
        throw $this->createNotFoundException('Visit not found.');
    }

    // Debug: Check if the CSRF token is valid
    if ($this->isCsrfTokenValid('delete' . $visite->getId(), $request->request->get('_token'))) {
        // Remove the visit
        $entityManager->remove($visite);
        $entityManager->flush();

        // Add a success message
        $this->addFlash('success', 'Visit deleted successfully.');
    } else {
        // Add an error message for invalid CSRF token
        $this->addFlash('error', 'Invalid CSRF token.');
    }

    // Redirect to the visite_index route
    return $this->redirectToRoute('visite_index', ['id' => $dossierId]);
}
#[Route('/telecharger-diagnostic/{id}', name: 'visite_telecharger_diagnostic', methods: ['GET'])]
    public function telechargerDiagnostic(Visite $visite, Security $security, DossierMedicalRepository $dossierMedicalRepository): Response
    {
        $medecin = $security->getUser(); // Médecin connecté
        $dossierMedical = $dossierMedicalRepository->find($visite->getDossierMedical()->getId());
        $patient = $dossierMedical ? $dossierMedical->getPatient() : null;

        return $this->genererPdf($visite->getDiagnostic(), 'diagnostic.pdf', $medecin, $patient);
    }

    #[Route('/telecharger-ordonnance/{id}', name: 'visite_telecharger_ordonnance', methods: ['GET'])]
    public function telechargerOrdonnance(Visite $visite, Security $security, DossierMedicalRepository $dossierMedicalRepository): Response
    {
        $medecin = $security->getUser(); // Médecin connecté
        $dossierMedical = $dossierMedicalRepository->find($visite->getDossierMedical()->getId());
        $patient = $dossierMedical ? $dossierMedical->getPatient() : null;

        return $this->genererPdf($visite->getOrdonnance(), 'ordonnance.pdf', $medecin, $patient);
    }

    #[Route('/telecharger-certificat/{id}', name: 'visite_telecharger_certificat', methods: ['GET'])]
    public function telechargerCertificat(Visite $visite, Security $security, DossierMedicalRepository $dossierMedicalRepository): Response
    {
        $medecin = $security->getUser(); // Médecin connecté
        $dossierMedical = $dossierMedicalRepository->find($visite->getDossierMedical()->getId());
        $patient = $dossierMedical ? $dossierMedical->getPatient() : null;

        return $this->genererPdf($visite->getCertificat(), 'certificat.pdf', $medecin, $patient);
    }

    private function genererPdf(string $contenu, string $nomFichier, Utilisateur $medecin, ?Utilisateur $patient): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true); // Permettre le chargement des images externes

        $dompdf = new Dompdf($options);

        // Vérifier si le patient existe
        $html = $this->renderView('visite/pdf_template.html.twig', [
            'contenu' => $contenu,
            'medecin_nom' => $medecin->getNom(),
            'medecin_prenom' => $medecin->getPrenom(),
            'patient_nom' => $patient ? $patient->getNom() : 'Inconnu',
            'patient_prenom' => $patient ? $patient->getPrenom() : 'Inconnu',
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $nomFichier . '"',
            ]
        );
    }
}

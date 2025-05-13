<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\EntretientChambre;
use App\Enum\Activechambre;
use App\Form\ChambreType;
use App\Form\EntretientChambreType;
use App\Repository\ChambreRepository;
use App\Repository\LitRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\ServiceRepository;

final class ChambreController extends AbstractController
{
    #[Route('/chambre', name: 'app_chambre')]
    public function index(ChambreRepository $repository): Response
    {
        return $this->render('chambre/index.html.twig', [
            'chambres' => $repository->findAll()
        ]);
    }

    #[Route('/chambre/nouveau', name: 'chambre.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager, ChambreRepository $repository): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de l'unicité du numéro de chambre
            if ($repository->findOneBy(['num' => $chambre->getNum()])) {
                $this->addFlash('danger', 'Ce numéro de chambre existe déjà.');
            } else {
                $manager->persist($chambre);
                $manager->flush();
                $this->addFlash('success', 'Chambre ajoutée avec succès.');
                return $this->redirectToRoute('app_chambre');
            }
        }

        return $this->render('chambre/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/chambre/edition/{id}', name: 'chambre.edit', methods: ['GET', 'POST'])]
    public function edit(Chambre $chambre, Request $request, EntityManagerInterface $manager, ChambreRepository $repository): Response
    {
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de l'unicité du numéro de chambre sauf pour elle-même
            $existingChambre = $repository->findOneBy(['num' => $chambre->getNum()]);
            if ($existingChambre && $existingChambre !== $chambre) {
                $this->addFlash('danger', 'Ce numéro de chambre est déjà utilisé.');
            } else {
                $manager->flush();
                $this->addFlash('success', 'Chambre modifiée avec succès.');
                return $this->redirectToRoute('app_chambre');
            }
        }

        return $this->render('chambre/edit.html.twig', [
            'form' => $form->createView(),
            'chambre' => $chambre
        ]);
    }

    #[Route('/chambre/suppression/{id}', name: 'chambre.supprimer', methods: ['GET'])]
    public function delete(EntityManagerInterface $manager, Chambre $chambre): Response
    {
        if (!$chambre) {
            $this->addFlash('danger', 'Chambre non trouvée.');
            return $this->redirectToRoute('app_chambre');
        }

        // Suppression des entretiens liés
        foreach ($chambre->getEntretient() as $entretient) {
            $manager->remove($entretient);
        }

        $manager->remove($chambre);
        $manager->flush();

        $this->addFlash('success', 'Chambre supprimée avec succès.');
        return $this->redirectToRoute('app_chambre');
   


    }
    #[Route('/chambre/{id}/entretients', name: 'chambre.entretients')]
public function showEntretients(Chambre $chambre): Response
{
    $entretients = $chambre->getEntretient();

    return $this->render('entretient_chambre/index.html.twig', [
        'chambre' => $chambre,
        'entretients' => $entretients,
    ]);
}
#[Route('/chambre/{id}/entretientsfront', name: 'chambre.entretientsfront')]
public function showEntretientsfront(Chambre $chambre): Response
{
    $entretients = $chambre->getEntretient();

    return $this->render('entretient_chambre/front/index.html.twig', [
        'chambre' => $chambre,
        'entretients' => $entretients,
    ]);
}
#[Route('/chambre/{id}/entretient/new', name: 'chambre.entretient.new', methods: ['GET', 'POST'])]
public function addEntretient(
    Chambre $chambre,
    Request $request,
    EntityManagerInterface $manager
): Response {
    $entretient = new EntretientChambre();
    $form = $this->createForm(EntretientChambreType::class, $entretient);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entretient->setChambre($chambre);

       
        $chambre->setActive('Occupée');
        $manager->persist($entretient);
        $manager->persist($chambre);
        $manager->flush();

       
        $dateFin = $entretient->getDateFin();
        if ($dateFin) {
            $this->planifierStatutDisponible($chambre, $dateFin, $manager);
        }

        $this->addFlash('success', 'Entretien ajouté et chambre mise à jour.');
        return $this->redirectToRoute('app_chambre');
    }

    return $this->render('entretient/new.html.twig', [
        'form' => $form->createView(),
        'chambre' => $chambre
    ]);
}

private function planifierStatutDisponible(Chambre $chambre, \DateTime $dateFin, EntityManagerInterface $manager)
{
    $delay = $dateFin->getTimestamp() - time();

    if ($delay > 0) {
       
        sleep($delay);  
        $chambre->setActive('Disponible');
        $manager->flush();
    }
}
#[Route('/chambre/{id}/visualisation', name: 'chambre.visualisation')]
public function visualisation(Chambre $chambre): Response
{   $lits = $chambre->getLits()->toArray();
    $litsArray = array_map(fn($lit) => [
        'id' => $lit->getId(),
        'positionX' => rand(0, 4),  
        'positionZ' => rand(-4, 0),
        'status' => $lit->getType()
    ], $lits);
    return $this->render('chambre/visualisation.html.twig', [
        'chambre' => $chambre,
        'lits' => $litsArray
    ]);
}
#[Route('/chambre/{id}/lits', name: 'chambre.lits')]
public function showLits(Chambre $chambre): Response
{
    $lits = $chambre->getLits();

    return $this->render('lit/index.html.twig', [
        'chambre' => $chambre,
        'lits' => $lits,
    ]);
}
#[Route('/chambre/{id}/litsfront', name: 'chambre.litsfront')]
public function showLitsfront(Chambre $chambre): Response
{
    $lits = $chambre->getLits();

    return $this->render('lit/frontoffice/index.html.twig', [
        'chambre' => $chambre,
        'lits' => $lits,
    ]);
}
#[Route('/chambre/{id}/service', name: 'chambre.service', methods: ['GET'])]
    public function showChambre(Chambre $chambre): Response
    {
        $service = $chambre->getPosition();

        return $this->render('chambre/index.html.twig', [
           
            'chambre' => $chambre,
            'service' => $service,
        ]);
    }
    #[Route('/frontoffice/chambre', name: 'app_chambrefront')]
    public function Showoff(ChambreRepository $repository,ServiceRepository $serviceRepository): Response
    {
       
        return $this->render('chambre/frontoffice/index.html.twig', [
            'chambres' => $repository->findAll(),
            'services' => $serviceRepository->findAll(),
        ]);
    }
    #[Route('/chambre/guide', name: 'chambre.guide')]
public function guide(ChambreRepository $repository): Response
{
    $chambres = $repository->findAll();

    return $this->render('chambre/guide.html.twig', [
        'chambres' => $chambres,
    ]);
}
#[Route('/chambre/guide/download-pdf', name: 'chambre.guide.download_pdf')]
public function downloadGuidePdf(ChambreRepository $repository): Response
{
    $chambres = $repository->findAll();

    $html = $this->renderView('chambre/guide_pdf.html.twig', [
        'chambres' => $chambres
    ]);

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $output = $dompdf->output();
    $response = new Response($output);
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment; filename="guide_chambres.pdf"');

    return $response;
}
#[Route('/chambre/recherche', name: 'chambre.recherche', methods: ['GET'])]
public function recherche(Request $request, ChambreRepository $repository): Response
{
    $searchTerm = $request->query->get('search', '');

    $chambres = $repository->findBySearchTerm($searchTerm);

    return $this->render('chambre/index.html.twig', [
        'chambres' => $chambres,
        'searchTerm' => $searchTerm
    ]);
}
#[Route('/chambre/recherchefront', name: 'chambre.recherchefront', methods: ['GET'])]
public function recherchefront(Request $request, ChambreRepository $repository): Response
{
    $searchTerm = $request->query->get('search', '');

    $chambres = $repository->findBySearchTerm($searchTerm);

    return $this->render('chambre/frontoffice/index.html.twig', [
        'chambres' => $chambres,
        'searchTerm' => $searchTerm
    ]);
}
#[Route('/responsable', name: 'app_responsable')]
public function stat(ChambreRepository $chambreRepository, LitRepository $litRepository): Response
{
    // Statistiques des chambres
    $totalChambres = $chambreRepository->count([]);
    $chambresDisponibles = $chambreRepository->count(['active' => 'Disponible']);
    $chambresOccupees = $chambreRepository->count(['active' => 'Occupée']);
    $chambresMaintenance = $chambreRepository->count(['active' => 'Maintenance']);

    // Statistiques des lits
    $totalLits = $litRepository->count([]);
    $litsDisponibles = $litRepository->count(['status' => 'libre']);
    $litsOccupes = $totalLits - $litsDisponibles;

    return $this->render('responsable/dashboard.html.twig', [
        'totalChambres' => $totalChambres,
        'chambresDisponibles' => $chambresDisponibles,
        'chambresOccupees' => $chambresOccupees,
        'chambresMaintenance' => $chambresMaintenance,
        'totalLits' => $totalLits,
        'litsDisponibles' => $litsDisponibles,
        'litsOccupes' => $litsOccupes,
        'lits' => $litRepository->findAll(), // Liste des lits pour la section des cartes
    ]);

}
#[Route('/chambre/filtre', name: 'chambre.filtre', methods: ['GET'])]
public function filtre(Request $request, ChambreRepository $repository, ServiceRepository $serviceRepository): Response
{
    // Récupérer les paramètres de filtrage
    $filters = [
        'type' => $request->query->get('type'),
        'active' => $request->query->get('active'),
        'capaciteMin' => $request->query->getInt('capaciteMin'),
        'capaciteMax' => $request->query->getInt('capaciteMax'),
        'service' => $request->query->getInt('service'),
        'search' => $request->query->get('search')
    ];
   
    // Filtrer les valeurs vides
    $filters = array_filter($filters, function($value) {
        return $value !== null && $value !== '';
    });
   
    // Récupérer les chambres filtrées
    $chambres = $repository->findByFilters($filters);
   
    // Si c'est une requête AJAX, renvoyer le HTML partiel
    if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
        return $this->render('chambre/_liste.html.twig', [
            'chambres' => $chambres,
            'filters' => $filters  // Ajouter filters ici
        ]);
    }
   
    // Pour les requêtes normales, rendre la page complète
    return $this->render('chambre/index.html.twig', [
        'chambres' => $chambres,
        'services' => $serviceRepository->findAll(),
        'filters' => $filters
    ]);
}
#[Route('/chambre/tri/{sortBy}/{order}', name: 'chambre.tri', methods: ['GET'])]
public function listChambres(
    ChambreRepository $chambreRepository,
    Request $request
): Response {
    // Récupérer les paramètres de tri
    $sortBy = $request->query->get('sortBy', 'num');
        $sortDirection = $request->query->get('sortDirection', 'ASC');
        $searchTerm = $request->query->get('search', '');

    // Récupérer les chambres triées
    $chambres = $chambreRepository->findBySearchCriteriaSorted(
        $searchTerm,
        $sortBy,
        $sortDirection
    );

    return $this->render('chambre/frontoffice/index.html.twig', [
        'chambres' => $chambres,
        'searchTerm' => $searchTerm,
        'sortBy' => $sortBy,
        'sortDirection' => $sortDirection
    ]);
}


   

   

}
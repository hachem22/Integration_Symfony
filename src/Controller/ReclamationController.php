<?php

namespace App\Controller;
use App\Enum\ReclamationStatut;
use App\Entity\TraitementReclamation; 
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\SwearWordFilter;
use Symfony\Component\HttpFoundation\JsonResponse; // Add this for JSON responses
use Dompdf\Dompdf;
use ChartJs\ChartJS;
#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    private $swearWordFilter;

    public function __construct( SwearWordFilter $swearWordFilter)
    {
      
        $this->swearWordFilter = $swearWordFilter;
    }
    #[Route('/', name: 'reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository, CategorieRepository $categorieRepository): Response
    {
        $utilisateur = $this->getUser(); // Récupérer l'utilisateur connecté
    
        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos réclamations.');
        }
    
        $statut = $request->query->get('statut');
        $type = $request->query->get('type');
        $categorieId = $request->query->get('categorie');
    
        // Récupérer seulement les réclamations de l'utilisateur connecté
        $reclamations = $reclamationRepository->searchReclamations($statut, $type, $categorieId, $utilisateur);
        $categories = $categorieRepository->findAll(); // Liste des catégories
    
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'categories' => $categories,
            'selectedStatut' => $statut,
            'selectedType' => $type,
            'selectedCategorie' => $categorieId
        ]);
    }
     #[Route('/pharmacien', name: 'reclamation_index1', methods: ['GET'])]
    public function index1(Request $request, ReclamationRepository $reclamationRepository, CategorieRepository $categorieRepository): Response
    {
        $utilisateur = $this->getUser(); // Récupérer l'utilisateur connecté
    
        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos réclamations.');
        }
    
        $statut = $request->query->get('statut');
        $type = $request->query->get('type');
        $categorieId = $request->query->get('categorie');
    
        // Récupérer seulement les réclamations de l'utilisateur connecté
        $reclamations = $reclamationRepository->searchReclamations($statut, $type, $categorieId, $utilisateur);
        $categories = $categorieRepository->findAll(); // Liste des catégories
    
        return $this->render('reclamationpharmacien/index.html.twig', [
            'reclamations' => $reclamations,
            'categories' => $categories,
            'selectedStatut' => $statut,
            'selectedType' => $type,
            'selectedCategorie' => $categorieId
        ]);
    }
    
    #[Route('/admin', name: 'reclamation_indexx', methods: ['GET'])]
public function indexx(
    Request $request,
    ReclamationRepository $reclamationRepository,
    CategorieRepository $categorieRepository,
    PaginatorInterface $paginator // Injecter le service Paginator
): Response
{
    $statut = $request->query->get('statut');
    $type = $request->query->get('type');
    $categorieId = $request->query->get('categorie');

    // Récupérer toutes les réclamations (pour l'admin)
    $queryBuilder = $reclamationRepository->createSearchQueryBuilder($statut, $type, $categorieId);

    // Paginer les résultats
    $reclamations = $paginator->paginate(
        $queryBuilder, // Requête ou QueryBuilder
        $request->query->getInt('page', 1), // Numéro de page (par défaut 1)
        5 // Nombre d'éléments par page
    );

    // Récupérer les catégories pour le filtre
    $categories = $categorieRepository->findAll();

    return $this->render('reclamationadmin/index.html.twig', [
        'reclamations' => $reclamations,
        'categories' => $categories,
        'selectedStatut' => $statut,
        'selectedType' => $type,
        'selectedCategorie' => $categorieId,
    ]);
}

#[Route('/stat', name: 'reclamation_stat', methods: ['GET'])]
public function stat(Request $request, ReclamationRepository $reclamationRepository): Response
{
    // Récupérer les statistiques pour les graphiques
    $totalReclamations = $reclamationRepository->countTotalReclamations();
    $reclamationsEnAttente = $reclamationRepository->countReclamationsByStatut('en_attente');
    $reclamationsEnCours = $reclamationRepository->countReclamationsByStatut('en_cours');
    $reclamationsResolues = $reclamationRepository->countReclamationsByStatut('résolue');
    $reclamationsService = $reclamationRepository->countReclamationsByType('Service');
    $reclamationsSysteme = $reclamationRepository->countReclamationsByType('Système');
    $reclamationsParJour = $reclamationRepository->countReclamationsByDay();
    $reclamationsParMois = $reclamationRepository->countReclamationsByMonth();

    return $this->render('reclamationadmin/stat.html.twig', [
        'totalReclamations' => $totalReclamations,
        'reclamationsEnAttente' => $reclamationsEnAttente,
        'reclamationsEnCours' => $reclamationsEnCours,
        'reclamationsResolues' => $reclamationsResolues,
        'reclamationsService' => $reclamationsService,
        'reclamationsSysteme' => $reclamationsSysteme,
        'reclamationsParJour' => $reclamationsParJour,
        'reclamationsParMois' => $reclamationsParMois,
    ]);
}

#[Route('/new', name: 'reclamation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ReclamationRepository $reclamationRepository): Response
{
    $utilisateur = $this->getUser();

    // Vérifier que l'utilisateur est connecté
    if (!$utilisateur) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour faire une réclamation.');
    }

    // Vérifier le nombre de réclamations déjà faites aujourd'hui
    $nbReclamations = $reclamationRepository->countReclamationsToday($utilisateur);
    if ($nbReclamations >= 3) {
        $this->addFlash('error', 'Vous avez atteint la limite de 3 réclamations pour aujourd\'hui.');
        return $this->redirectToRoute('reclamation_index');
    }

    // Création de la réclamation
    $reclamation = new Reclamation();
    $reclamation->setStatut(ReclamationStatut::EN_ATTENTE);
    $reclamation->setDatecreation(new \DateTime());
    $reclamation->setUtilisateur($utilisateur);

    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Check for swear words in the description
        $description = $reclamation->getDescription();
        if ($this->swearWordFilter->containsSwearWords($description)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Uh-oh! It looks like your post contains some inappropriate language. Let’s keep the conversation kind and constructive!',
            ]);
        }

        // Save the reclamation
        $entityManager->persist($reclamation);
        $entityManager->flush();

        // Return success response
        return new JsonResponse([
            'success' => true,
            'message' => 'Réclamation créée avec succès.',
            'redirect' => $this->generateUrl('reclamation_index'), // URL de redirection
        ]);
    }

    return $this->render('reclamation/new.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form->createView(),
    ]);
}
#[Route('pharmacien/new', name: 'reclamation_new1', methods: ['GET', 'POST'])]
public function new1(Request $request, EntityManagerInterface $entityManager, ReclamationRepository $reclamationRepository): Response
{
    $utilisateur = $this->getUser();

    // Vérifier que l'utilisateur est connecté
    if (!$utilisateur) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour faire une réclamation.');
    }

    // Vérifier le nombre de réclamations déjà faites aujourd'hui
    $nbReclamations = $reclamationRepository->countReclamationsToday($utilisateur);
    if ($nbReclamations >= 3) {
        $this->addFlash('error', 'Vous avez atteint la limite de 3 réclamations pour aujourd\'hui.');
        return $this->redirectToRoute('reclamation_index1');
    }

    // Création de la réclamation
    $reclamation = new Reclamation();
    $reclamation->setStatut(ReclamationStatut::EN_ATTENTE);
    $reclamation->setDatecreation(new \DateTime());
    $reclamation->setUtilisateur($utilisateur);

    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Check for swear words in the description
        $description = $reclamation->getDescription();
        if ($this->swearWordFilter->containsSwearWords($description)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Uh-oh! It looks like your post contains some inappropriate language. Let’s keep the conversation kind and constructive!',
            ]);
        }

        // Save the reclamation
        $entityManager->persist($reclamation);
        $entityManager->flush();

        // Return success response
        return new JsonResponse([
            'success' => true,
            'message' => 'Réclamation créée avec succès.',
            'redirect' => $this->generateUrl('reclamation_index1'), // URL de redirection
        ]);
    }

    return $this->render('reclamationpharmacien/new.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form->createView(),
    ]);
}
    
    #[Route('/{id}', name: 'reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        // Récupérer les traitements associés à la réclamation
        $traitements = $reclamation->getTraitementReclamations();
    
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'traitements' => $traitements, // Passer les traitements au template
        ]);
    }
    #[Route('pharmacien/{id}', name: 'reclamation_show1', methods: ['GET'])]
    public function show1(Reclamation $reclamation): Response
    {
        // Récupérer les traitements associés à la réclamation
        $traitements = $reclamation->getTraitementReclamations();
    
        return $this->render('reclamationpharmacien/show.html.twig', [
            'reclamation' => $reclamation,
            'traitements' => $traitements, // Passer les traitements au template
        ]);
    }
    #[Route('/admin/{id}', name: 'reclamation_showw', methods: ['GET'])]
    public function showw(Reclamation $reclamation): Response
    {
        // Récupérer les traitements associés à la réclamation
        $traitements = $reclamation->getTraitementReclamations();
    
        return $this->render('reclamationadmin/show.html.twig', [
            'reclamation' => $reclamation,
            'traitements' => $traitements, // Passer les traitements au template
        ]);
    }

    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
            'button_label' => 'Mettre à jour',
        ]);
    }
    #[Route('pharmacien/{id}/edit', name: 'reclamation_edit1', methods: ['GET', 'POST'])]
    public function edit1(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_index1');
        }

        return $this->render('reclamationpharmacien/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
            'button_label' => 'Mettre à jour',
        ]);
    }

    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reclamation_index');
    }
     #[Route('pharmacien/{id}', name: 'reclamation_delete1', methods: ['POST'])]
    public function delete1(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reclamation_index1');
    }
    #[Route('/admin/search', name: 'reclamation_search', methods: ['GET'])]
public function search(Request $request, ReclamationRepository $reclamationRepository): JsonResponse
{
    // Récupérer la valeur de recherche globale
    $globalSearch = $request->query->get('globalSearch');

    // Construire la requête de recherche basée sur le critère global
    $qb = $reclamationRepository->createQueryBuilder('r');

    if ($globalSearch) {
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('r.description', ':search'),
                $qb->expr()->like('r.cible', ':search'),
                $qb->expr()->like('r.type', ':search'),
                $qb->expr()->like('r.statut', ':search'),
                $qb->expr()->like('r.utilisateur.email', ':search')
            )
        )
        ->setParameter('search', '%' . $globalSearch . '%');
    }

    $reclamations = $qb->getQuery()->getResult();

    // Formater les résultats pour la réponse JSON
    $results = [];
    foreach ($reclamations as $reclamation) {
        $results[] = [
            'id' => $reclamation->getId(),
            'description' => $reclamation->getDescription(),
            'cible' => $reclamation->getCible(),
            'categorie' => $reclamation->getCategorie()->getNom(),
            'type' => $reclamation->getType(),
            'statut' => $reclamation->getStatut(),
            'datecreation' => $reclamation->getDatecreation()->format('d/m/Y H:i'),
            'utilisateur' => $reclamation->getUtilisateur()->getEmail(),
        ];
    }

    // Retourner les résultats filtrés sous forme de réponse JSON
    return new JsonResponse($results);
}


    
    
    #[Route('/traitement/{id}/rate', name: 'traitement_rate', methods: ['POST'])]
    public function rateTraitement(TraitementReclamation $traitement, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rating = $request->request->get('rating');
        if ($rating !== null && is_numeric($rating)) {
            $traitement->addRating((float)$rating); // Utilisez la méthode addRating pour mettre à jour la note
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('reclamation_show', ['id' => $traitement->getReclamation()->getId()]);
    }
     #[Route('/traitement/{id}/rate', name: 'traitement_rate1', methods: ['POST'])]
    public function rateTraitement1(TraitementReclamation $traitement, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rating = $request->request->get('rating');
        if ($rating !== null && is_numeric($rating)) {
            $traitement->addRating((float)$rating); // Utilisez la méthode addRating pour mettre à jour la note
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('reclamation_show1', ['id' => $traitement->getReclamation()->getId()]);
    }
    #[Route('/stat/pdf', name: 'reclamation_stat_pdf', methods: ['GET'])]
    public function statPdf(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        // Récupérer les statistiques
        $reclamationsEnAttente = $reclamationRepository->countReclamationsByStatut('en_attente');
        $reclamationsEnCours = $reclamationRepository->countReclamationsByStatut('en_cours');
        $reclamationsResolues = $reclamationRepository->countReclamationsByStatut('résolue');
        $reclamationsService = $reclamationRepository->countReclamationsByType('Service');
        $reclamationsSysteme = $reclamationRepository->countReclamationsByType('Système');
        $reclamationsParJour = $reclamationRepository->countReclamationsByDay();
        $reclamationsParMois = $reclamationRepository->countReclamationsByMonth();
    
        // Rendre le template Twig en HTML
        $html = $this->renderView('reclamationadmin/stat_pdf.html.twig', [
            'reclamationsEnAttente' => $reclamationsEnAttente,
            'reclamationsEnCours' => $reclamationsEnCours,
            'reclamationsResolues' => $reclamationsResolues,
            'reclamationsService' => $reclamationsService,
            'reclamationsSysteme' => $reclamationsSysteme,
            'reclamationsParJour' => $reclamationsParJour,
            'reclamationsParMois' => $reclamationsParMois,
        ]);
    
        // Configurer Dompdf
        $dompdf = new Dompdf();
    
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
    
        // Configurer le format du PDF (A4, portrait)
        $dompdf->setPaper('A4', 'portrait');
    
        // Rendre le PDF
        $dompdf->render();
    
        // Générer le PDF et le renvoyer en réponse
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="statistiques_reclamations.pdf"');
    
        return $response;
    }
    
    
}

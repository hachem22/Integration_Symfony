<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\UtilisateurEditType;
use App\Enum\UtilisateurRole;
use App\Repository\UtilisateurRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;
use App\Entity\RendezVous;
use App\Enum\NomService;
use App\Repository\ChambreRepository;
use App\Repository\LitRepository;
use App\Repository\StockPharmacieRepository;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function bin2hex;
use function random_bytes;
use function sprintf;
use function uniqid;
#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{
    private LoggerInterface $logger;
    private $entityManager;
    private $rendezVousRepository;
    private $utilisateurRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        RendezVousRepository $rendezVousRepository,
        UtilisateurRepository $utilisateurRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->rendezVousRepository = $rendezVousRepository;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->logger = $logger;
    }


  
    #[Route('/HomePatient', name: 'Home_Patient')]
    public function HomePatient(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PATIENT');
        $user = $this->getUser();

        // Ensure the user is logged in
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        return $this->render('patient/homepatient.html.twig', [
            'patient' => $user,
        ]);
    }
   
      #[Route('/HomeResponsable', name: 'Home_Responsable')]
public function stat(ChambreRepository $chambreRepository, LitRepository $litRepository): Response
{
    $this->denyAccessUnlessGranted('ROLE_RESPONSABLE');
    $user = $this->getUser();
    // Statistiques des chambres
    $totalChambres = $chambreRepository->count([]);
    $chambresDisponibles = $chambreRepository->count(['active' => 'Disponible']);
    $chambresOccupees = $chambreRepository->count(['active' => 'Occupée']);
    $chambresMaintenance = $chambreRepository->count(['active' => 'Maintenance']);

    // Statistiques des lits
    $totalLits = $litRepository->count([]);
    $litsDisponibles = $litRepository->count(['status' => 'libre']);
    $litsOccupes = $totalLits - $litsDisponibles;

    if (!$user instanceof Utilisateur) {
        throw $this->createAccessDeniedException('You must be logged in to view this page.');
    }
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


    #[Route('/HomeMedecin', name: 'Home_Medecin')]
    public function homeMedecin(StockPharmacieRepository $stockPharmacieRepository): Response
    {
        // Ensure only médecins can access this page
        $this->denyAccessUnlessGranted('ROLE_MEDECIN');

        // Get the current logged-in médecin
        $medecin = $this->getUser();
        if (!$medecin instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Fetch today's rendez-vous for this médecin
        $today = new \DateTime('today');
        $todayRendezVous = $this->rendezVousRepository->createQueryBuilder('rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date = :today')
            ->setParameter('medecin', $medecin)
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        // Calculate total rendez-vous for the month
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month');
        $monthlyRendezVous = $this->rendezVousRepository->createQueryBuilder('rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date BETWEEN :start AND :end')
            ->setParameter('medecin', $medecin)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getResult();

        // Patient statistics
        $totalPatients = $this->countTotalPatients($medecin);
        $newPatientsLastWeek = $this->countNewPatients($medecin);

        // Consultation statistics
        $statsConsultations = count($monthlyRendezVous);
        $statsConsultationsDetails = $this->getDetailedConsultationStats($monthlyRendezVous);

        // Next rendez-vous
        $nextRendezVous = $this->findNextRendezVous($medecin);

        // Notifications
        $notifications = $this->prepareNotifications($medecin);
        $rendezVousStatusStats = $this->getRendezVousStatusStats($medecin);
        return $this->render('medecin/HomeMedecin.html.twig', [
            'rendezVousAujourdhui' => count($todayRendezVous),
            'totalRendezVous' => count($monthlyRendezVous),
            'nouveauxRendezVous' => $this->countNewRendezVous($medecin),
            'totalPatients' => $totalPatients,
            'nouveauxPatients' => $newPatientsLastWeek,
            'statsConsultations' => $statsConsultations,
            'statsConsultationsDetails' => $statsConsultationsDetails,
            'prochainRdvTemps' => $this->formatNextRendezVous($nextRendezVous),
            'rendezVousStatusStats' => $rendezVousStatusStats,  // Ajout des statistiques de statut
            'medicaments' => $stockPharmacieRepository->findAll(),
            'notifications' => $notifications,
            
        ]);
    }

    // Count total patients for this medecin
    private function countTotalPatients(Utilisateur $medecin): int
    {
        return $this->utilisateurRepository->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->innerJoin('u.rendezVousPris', 'rv')
            ->where('rv.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Count new patients in the last week
    private function countNewPatients(Utilisateur $medecin): int
    {
        $weekAgo = new \DateTime('-7 days');

        return $this->utilisateurRepository->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->innerJoin('u.rendezVousPris', 'rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date >= :weekAgo')
            ->setParameter('medecin', $medecin)
            ->setParameter('weekAgo', $weekAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Count new rendez-vous in the last week
    private function countNewRendezVous(Utilisateur $medecin): int
    {
        $weekAgo = new \DateTime('-7 days');

        return $this->rendezVousRepository->createQueryBuilder('rv')
            ->select('COUNT(rv.id)')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date >= :weekAgo')
            ->setParameter('medecin', $medecin)
            ->setParameter('weekAgo', $weekAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Get detailed consultation statistics
    private function getDetailedConsultationStats(array $monthlyRendezVous): array
    {
        $statsTypes = [
            'Consultation générale' => 0,
            'Suivi traitement' => 0,
            'Urgence' => 0,
            'Autre' => 0
        ];

        foreach ($monthlyRendezVous as $rendezVous) {
            $service = $rendezVous->getService();
            $type = $service instanceof NomService ? $service->getNom() : 'Autre';
            if (isset($statsTypes[$type])) {
                $statsTypes[$type]++;
            } else {
                $statsTypes['Autre']++;
            }
        }

        return $statsTypes;
    }

    // Find next rendez-vous
    private function findNextRendezVous(Utilisateur $medecin): ?RendezVous
    {
        $now = new \DateTime('now');
        
        return $this->rendezVousRepository->createQueryBuilder('rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date > :now')
            ->setParameter('medecin', $medecin)
            ->setParameter('now', $now)
            ->orderBy('rv.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Format next rendez-vous time
    private function formatNextRendezVous(?RendezVous $rendezVous): string
    {
        if (!$rendezVous) {
            return 'Aucun rendez-vous à venir';
        }

        $now = new \DateTime('now');
        $diff = $now->diff($rendezVous->getDate());

        if ($diff->days == 0) {
            return sprintf('%d heures et %d minutes', $diff->h, $diff->i);
        } elseif ($diff->days == 1) {
            $heure = $rendezVous->getHeure();
            return 'Demain à ' . ($heure instanceof \DateTime ? $heure->format('H:i') : 'Heure non définie');
        } else {
            $heure = $rendezVous->getHeure();
            return sprintf('Le %s à %s', 
                $rendezVous->getDate()->format('d/m/Y'), 
                $heure instanceof \DateTime ? $heure->format('H:i') : 'Heure non définie'
            );
        }
    }
    private function getRendezVousStatusStats(Utilisateur $medecin): array
    {
        $statuses = [
            'En Attente' => 0,
            'Accepté' => 0,
            'Refusé' => 0,
            'Terminé' => 0
        ];
    
        $rendezVousList = $this->rendezVousRepository->createQueryBuilder('rv')
            ->where('rv.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->getQuery()
            ->getResult();
    
        foreach ($rendezVousList as $rendezVous) {
            // Utiliser getRendezVousStatus() qui est la méthode correcte dans l'entité
            $status = $rendezVous->getRendezVousStatus() ?? 'En Attente';
            if (isset($statuses[$status])) {
                $statuses[$status]++;
            }
        }
    
        return $statuses;
    }
    // Prepare notifications
    private function prepareNotifications(Utilisateur $medecin): array
    {
        $today = new \DateTime('now');
        
        // Fetch upcoming appointments
        $upcomingAppointments = $this->rendezVousRepository->createQueryBuilder('rv')
            ->where('rv.medecin = :medecin')
            ->andWhere('rv.date >= :today')
            ->setParameter('medecin', $medecin)
            ->setParameter('today', $today)
            ->orderBy('rv.date', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $notifications = [];

        // Upcoming appointments notifications
        foreach ($upcomingAppointments as $appointment) {
            $heure = $appointment->getHeure();
            $heureFormatted = $heure instanceof \DateTime ? $heure->format('H:i') : 'Heure non définie';
            $notifications[] = [
                'type' => 'Rendez-vous',
                'title' => 'Prochain Rendez-vous',
                'description' => sprintf(
                    'Rendez-vous avec %s le %s à %s', 
                    $appointment->getPatient()->getNom(),
                    $appointment->getDate()->format('d/m/Y'),
                    $heureFormatted
                ),
                'priority' => 'normal'
            ];
        }

        return $notifications;
    }



    #[Route(name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    MailerInterface $mailer,
    UserPasswordHasherInterface $passwordHasher
): Response {
    // Create new user
    $utilisateur = new Utilisateur();
    $form = $this->createForm(UtilisateurType::class, $utilisateur);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle file upload
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            // Generate a unique filename
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            // Move the file to the directory where images are stored
            $imageFile->move(
                $this->getParameter('images_directory'), // Define this parameter in services.yaml
                $newFilename
            );

            // Save the filename to the entity
            $utilisateur->setImage($newFilename);

            // Compute and store the face encoding
            $imagePath = $this->getParameter('images_directory') . '/' . $newFilename;
            $faceEncoding = $this->computeFaceEncoding($imagePath);
            $utilisateur->setFaceEncoding($faceEncoding);
        }

        // Generate random password
        $plainPassword = bin2hex(random_bytes(4)); // Generates an 8-character password

        // Hash the password
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
        $utilisateur->setPassword($hashedPassword);

        // Persist the user entity
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        // Send email with the hashed password
        $email = (new Email())
            ->from('haythemdridi@gmail.com') // Sender email
            ->to($utilisateur->getEmail()) // Recipient email
            ->subject('Your Account Credentials')
            ->html("
                <p>Hello {$utilisateur->getNom()},</p>
                <p>Your account has been successfully created. Here are your login details:</p>
                <ul>
                    <li><strong>Email:</strong> {$utilisateur->getEmail()}</li>
                    <li><strong>Password:</strong> {$plainPassword}</li>
                </ul>
                <p>Please change your password after logging in.</p>
            ");

        // Send the email
        $mailer->send($email);

        // Redirect after email is sent
        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('utilisateur/new.html.twig', [
        'utilisateur' => $utilisateur,
        'form' => $form->createView(),
    ]);
}
private function computeFaceEncoding(string $imagePath): ?array
{
    // Call the Python script to compute face encoding
    $pythonScriptPath = $this->getParameter('kernel.project_dir') . '/scripts/face_encoding.py';
    $process = new Process(['python3', $pythonScriptPath, $imagePath]);
    $process->run();

    // Check if the process was successful
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    // Decode the JSON output from the Python script
    $result = json_decode($process->getOutput(), true);

    return $result['face_encoding'] ?? null;
}


    #[Route('/utilisateur/{id}', name: 'app_utilisateur_show')]
    public function show(int $id, UtilisateurRepository $repository): Response
    {
        $utilisateur = $repository->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/medecins', name: 'app_medecin_list')]
    public function medecinList(UtilisateurRepository $utilisateurRepository): Response
    {
        $medecins = $utilisateurRepository->findBy(['utilisateurRole' => UtilisateurRole::Medecin]);

        return $this->render('utilisateur/medecinList.html.twig', [
            'medecins' => $medecins,
        ]);
    }
    #[Route('/responsables', name: 'app_responsable_list')]
    public function responsableList(UtilisateurRepository $utilisateurRepository): Response
    {
        $responsables = $utilisateurRepository->findBy(['utilisateurRole' => UtilisateurRole::Responsable]);

        return $this->render('utilisateur/responsableList.html.twig', [
            'responsables' => $responsables,
        ]);
    }
    #[Route('/pharmaciens', name: 'app_pharmacien_list')]
    public function pharmacienList(UtilisateurRepository $utilisateurRepository): Response
    {
        $pharmaciens = $utilisateurRepository->findBy(['utilisateurRole' => UtilisateurRole::Pharmacien]);

        return $this->render('utilisateur/PharmacienList.html.twig', [
            'pharmaciens' => $pharmaciens,
        ]);
    }
    #[Route('/femmeDeMenages', name: 'app_femmeDeMenage_list')]
    public function femmeDeMenagesList(UtilisateurRepository $utilisateurRepository): Response
    {
        $femmesDeMenage  = $utilisateurRepository->findBy(['utilisateurRole' => UtilisateurRole::FemmeDeMenage]);

        return $this->render('utilisateur/femmeDeMenagesList.html.twig', [
            'femmesDeMenage' => $femmesDeMenage,
        ]);
    }
    #[Route('/patients', name: 'app_patient_list')]
    public function patientsList(UtilisateurRepository $utilisateurRepository): Response
    {
        $patients  = $utilisateurRepository->findBy(['utilisateurRole' => UtilisateurRole::Patient]);

        return $this->render('utilisateur/patientsList.html.twig', [
            'patients' => $patients,
        ]);
    }
    #[Route('/infirmiers', name: 'app_infirmier_list')]
    public function infirmiersList(UtilisateurRepository $utilisateurRepository): Response
    {
        $infirmiers  = $utilisateurRepository->findBy(['utilisateurRole' => UtilisateurRole::Infirmier]);

        return $this->render('utilisateur/infirmiersList.html.twig', [
            'infirmiers' => $infirmiers,
        ]);
    }
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();

        // Ensure the user is logged in
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        // Pass the user data to the Twig template
        return $this->render('utilisateur/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/edit/{id}', name: 'app_utilisateur_edit_profil', methods: ['GET', 'POST'])]
public function editProfile(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(UtilisateurEditType::class, $utilisateur);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle the image upload
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            // Generate a unique filename
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the upload directory
            $imageFile->move(
                $this->getParameter('images_directory'), // Use the parameter here
                $newFilename
            );

            // Update the user's image in the database
            $utilisateur->setImage($newFilename);
        }

        // Save the changes to the database
        $entityManager->flush();

        return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('utilisateur/editProfil.html.twig', [
        'user' => $utilisateur,
        'form' => $form->createView(),
    ]);
}

#[Route('/deleteProfil/{id}', name: 'app_utilisateur_delete_profil', methods: ['POST'])]
    public function deleteProfil(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('security /auth-login-basic.html.twig', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/profileUtilisateur', name: 'app_profile_utilisateur')]
    public function profileUtilisateur(): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();

        // Ensure the user is logged in
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('You must be logged in to view this page.');
        }

        // Pass the user data to the Twig template
        return $this->render('utilisateur/profileUtilisateur.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/editUtilisateur/{id}', name: 'app_utilisateur_edit_profil_utilisateur', methods: ['GET', 'POST'])]
    public function editProfileUtilisateur(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UtilisateurEditType::class, $utilisateur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Generate a unique filename
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
    
                // Move the file to the upload directory
                $imageFile->move(
                    $this->getParameter('images_directory'), // Use the parameter here
                    $newFilename
                );
    
                // Update the user's image in the database
                $utilisateur->setImage($newFilename);
            }
    
            // Save the changes to the database
            $entityManager->flush();
    
            return $this->redirectToRoute('app_profile_utilisateur', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('utilisateur/editProfilUtilisateur.html.twig', [
            'user' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }
        #[Route('/medecins/search', name: 'app_medecins_search', methods: ['POST'])]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $name = $request->request->get('name');
        $specialty = $request->request->get('specialty');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');

        // Build query based on search criteria
        $qb = $em->createQueryBuilder()
            ->select('u')
            ->from('App\Entity\Utilisateur', 'u')
            ->where('u.utilisateurRole = :role')
            ->setParameter('role', 'Medecin');

        if ($name) {
            $qb->andWhere('u.Nom LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
        if ($specialty) {
            $qb->andWhere('u.medecinSpecilaite = :specialty')
                ->setParameter('specialty', $specialty);
        }
        if ($email) {
            $qb->andWhere('u.Email LIKE :email')
                ->setParameter('email', '%' . $email . '%');
        }
        if ($phone) {
            $qb->andWhere('u.Tel LIKE :phone')
                ->setParameter('phone', '%' . $phone . '%');
        }

        $medecins = $qb->getQuery()->getResult();

        return $this->render('utilisateur/_medecins_list.html.twig', [
            'medecins' => $medecins
        ]);
    }
}
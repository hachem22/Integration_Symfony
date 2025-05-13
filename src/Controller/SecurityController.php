<?php

// src/Controller/SecurityController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        // Handle form submission
        if ($request->isMethod('POST')) {
            // Get the reCAPTCHA response
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
    
            // Verify reCAPTCHA
            $secretKey = '6LeznekqAAAAAP-qJPWuDrd0qgnIzUXPNkVPbTXm'; // Replace with your Secret Key
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
            ];
    
            // Send a POST request to Google's reCAPTCHA API
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ],
            ];
    
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $resultJson = json_decode($result);
    
            // Check if reCAPTCHA verification failed
            if (!$resultJson->success) {
                $this->addFlash('error', 'Please complete the reCAPTCHA.');
                return $this->redirectToRoute('app_login');
            }
        }
    
        // Existing login logic
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
    
        // Handle failed login attempts
        if ($error) {
            $user = $em->getRepository(Utilisateur::class)->findOneBy(['Email' => $lastUsername]); // Use 'Email' here
    
            if ($user) {
                $failedAttempts = $user->getFailedLoginAttempts() + 1;
                $user->setFailedLoginAttempts($failedAttempts);
    
                // Lock the account if the threshold is reached
                if ($failedAttempts >= 5) {
                    $user->setLockedUntil(new \DateTime('+15 minutes'));
    
                    // Send email notification
                    $email = (new TemplatedEmail())
                        ->from('haythemdridi633@gmail.com')
                        ->to($user->getEmail()) // Use getEmail() here
                        ->subject('Account Locked')
                        ->htmlTemplate('emails/account_locked.html.twig')
                        ->context([
                            'user' => $user,
                        ]);
    
                    $mailer->send($email);
    
                    $this->addFlash('error', 'Your account has been locked due to too many failed login attempts. Please try again later.');
                }
    
                $em->flush();
            }
        }
    
        // Check if the account is locked
        $user = $em->getRepository(Utilisateur::class)->findOneBy(['Email' => $lastUsername]); // Use 'Email' here
        if ($user && $user->getLockedUntil() > new \DateTime()) {
            $this->addFlash('error', 'Your account is locked. Please try again later.');
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        // Get the current user and ensure it's an instance of Utilisateur
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            throw new \RuntimeException('Expected an instance of Utilisateur.');
        }

        // Debugging: Check the type of the user object
        dump(get_class($user));

        // Debugging: Check if the method exists
        dump(method_exists($user, 'getUtilisateurRole'));

        // Debugging: Check the authenticated user's roles
        dump($user->getRoles());
        dump($user->getUtilisateurRole());

        // Redirect based on role
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_utilisateur_index');
        } elseif (in_array('ROLE_PHARMACIEN', $user->getRoles())) {
            return $this->redirectToRoute('app_test_dashboard');
        } elseif (in_array('ROLE_PATIENT', $user->getRoles())) {
            return $this->redirectToRoute('Home_Patient');
        }elseif (in_array('ROLE_MEDECIN', $user->getRoles())) {
            return $this->redirectToRoute('Home_Medecin');
        }elseif (in_array('ROLE_RESPONSABLE', $user->getRoles())) {
            return $this->redirectToRoute('Home_Responsable');
        }


        // Default fallback
        return $this->redirectToRoute('app_login');
    }
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
    }

    // src/Controller/SecurityController.php

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Find the user by email
            $user = $utilisateurRepository->findOneByEmail($email); // Use the repository method

            if ($user) {
                // Generate a reset token
                $resetToken = Uuid::v4()->toRfc4122();
                $user->setResetToken($resetToken);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour')); // Token expires in 1 hour

                $entityManager->flush();

                // Send the email
                $email = (new TemplatedEmail())
                    ->from('no-reply@yourdomain.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context([
                        'resetToken' => $resetToken,
                        'user' => $user,
                    ]);

                $mailer->send($email);

                $this->addFlash('success', 'A password reset link has been sent to your email.');
            } else {
                $this->addFlash('error', 'No account found with this email.');
            }
        }

        return $this->render('auth-forgot-password-basic.html.twig');
    }

    // src/Controller/SecurityController.php

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        string $token,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Find the user by the reset token
        $user = $utilisateurRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'Invalid or expired reset token.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');

            // Store the new password in plain text (not recommended for production)
            $user->setPassword($newPassword);

            // Clear the reset token
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $entityManager->flush();

            $this->addFlash('success', 'Your password has been reset successfully.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }

    #[Route('/face-login', name: 'app_face_login', methods: ['POST'])]
    public function faceLogin(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $faceEncoding = $data['face_encoding'];

    // Find the user with the closest face encoding
    $user = $utilisateurRepository->findByFaceEncoding($faceEncoding);

    if ($user) {
        // Log the user in
        return $this->json(['success' => true, 'user' => $user->getEmail()]);
    } else {
        return $this->json(['success' => false, 'error' => 'No matching user found'], 401);
    }
}
#[Route('/register-face', name: 'app_register_face', methods: ['POST'])]
public function registerFace(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $email = $data['email'];
    $faceEncoding = $data['face_encoding'];

    $user = $utilisateurRepository->findOneByEmail($email);
    if ($user) {
        $user->setFaceEncoding($faceEncoding);
        $utilisateurRepository->save($user, true);

        return $this->json(['success' => true]);
    } else {
        return $this->json(['success' => false, 'error' => 'User not found'], 404);
    }
}
}

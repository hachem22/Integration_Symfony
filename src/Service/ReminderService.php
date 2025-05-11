<?php

namespace App\Service;

use App\Entity\RendezVous;
use App\Repository\RendezVousRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ReminderService
{
    private $rendezVousRepository;
    private $mailer;
    private $twig;

    public function __construct(RendezVousRepository $rendezVousRepository, MailerInterface $mailer, Environment $twig)
    {
        $this->rendezVousRepository = $rendezVousRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendReminders(): void
    {
        $tomorrow = (new \DateTime())->modify('+1 day');

        // Récupérer les rendez-vous acceptés pour demain
        $rendezVousForTomorrow = $this->rendezVousRepository->findAcceptedForTomorrow($tomorrow);

        foreach ($rendezVousForTomorrow as $rendezVous) {
            $this->sendReminderEmail($rendezVous, 'Votre rendez-vous est demain');
        }
    }

    private function sendReminderEmail(RendezVous $rendezVous, string $subject): void
    {
        $patientEmail = $rendezVous->getPatient()->getEmail();
        $content = $this->twig->render('emails/rendezvous_rappel.html.twig', [
            'rendezVous' => $rendezVous,
        ]);

        $email = (new Email())
            ->from('your-email@example.com')
            ->to($patientEmail)
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);
    }
}
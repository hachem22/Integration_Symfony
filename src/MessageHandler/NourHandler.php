<?php
namespace App\MessageHandler;

use App\Message\Nour;  // Assure-toi que tu as bien ce namespace
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;



class NourHandler implements MessageHandlerInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(Nour $message)
{
    $email = (new Email())
        ->from('haythemdridi633@gmail.com')
        ->to($message->getEmail())
        ->subject('Votre événement a été créé')
        ->html("
            <p>Hello {$message->getName()},</p>
            <p>Votre événement a été créé avec succès :</p>
            <ul>
                <li><strong>Email:</strong> {$message->getEmail()}</li>
                <li><strong>Password:</strong> {$message->getPlainPassword()}</li>
            </ul>
            <p>Veuillez changer votre mot de passe après vous être connecté.</p>
        ");

    $this->mailer->send($email);
}

}

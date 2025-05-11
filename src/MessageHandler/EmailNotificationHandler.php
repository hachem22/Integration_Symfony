<?php

namespace App\MessageHandler;

use App\Message\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailNotificationHandler
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(EmailNotification $emailNotification)
    {
        $email = (new Email())
            ->from('your-email@example.com')
            ->to($emailNotification->getTo())
            ->subject($emailNotification->getSubject())
            ->html($emailNotification->getContent());

        $this->mailer->send($email);
    }
}
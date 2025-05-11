<?php

namespace App\MessageHandler;

use App\Message\UserCreationEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UserCreationEmailHandler implements MessageHandlerInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(UserCreationEmailMessage $message)
    {
        $email = (new Email())
            ->from('haythemdridi633.com')
            ->to($message->getEmail())
            ->subject('Your Account Has Been Created')
            ->html("
                <p>Hello {$message->getName()},</p>
                <p>Your account has been created successfully. Below are your login details:</p>
                <ul>
                    <li><strong>Email:</strong> {$message->getEmail()}</li>
                    <li><strong>Password:</strong> {$message->getPlainPassword()}</li>
                </ul>
                <p>Please change your password after logging in.</p>
            ");

        $this->mailer->send($email);
    }
}

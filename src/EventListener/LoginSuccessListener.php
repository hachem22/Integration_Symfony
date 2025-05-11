<?php
namespace App\EventListener;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof Utilisateur) {
            $user->setFailedLoginAttempts(0);
            $user->setLockedUntil(null);

            $this->entityManager->flush();
        }
    }
}
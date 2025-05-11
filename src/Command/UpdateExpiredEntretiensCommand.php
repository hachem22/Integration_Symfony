<?php

namespace App\Command;

use App\Repository\EntretientChambreRepository;
use App\Enum\StatutEntretientChambre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-expired-entretiens',
    description: 'Mettre à jour les entretiens expirés comme terminés',
)]
class UpdateExpiredEntretiensCommand extends Command
{
    private $repository;
    private $entityManager;

    public function __construct(EntretientChambreRepository $repository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();
        
        $io->info('Recherche des entretiens expirés...');
        
        $expiredEntretients = $this->repository->findExpiredEntretients($now);
        
        if (count($expiredEntretients) === 0) {
            $io->success('Aucun entretien expiré à mettre à jour.');
            return Command::SUCCESS;
        }
        
        $updated = 0;
        foreach ($expiredEntretients as $entretient) {
            $entretient->setStatut(StatutEntretientChambre::termine);
            
            // Si une chambre est associée, la remettre en état "disponible"
            $chambre = $entretient->getChambre();
            if ($chambre && $chambre->getActive() === 'maintenance') {
                $chambre->setActive('disponible');
            }
            
            $this->entityManager->persist($entretient);
            $updated++;
        }
        
        $this->entityManager->flush();
        
        $io->success("$updated entretien(s) mis à jour avec succès.");
        
        return Command::SUCCESS;
    }
}
<?php
namespace App\Command;

use App\Service\ChatbotHospitalierService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class TestChatbotCommand extends Command
{
    protected static $defaultName = 'app:chatbot:test';
    private $chatbotService;

    public function __construct(ChatbotHospitalierService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Tester le chatbot hospitalier')
            ->addOption('requete', 'r', InputOption::VALUE_REQUIRED, 'Requête à tester')
            ->addOption('langue', 'l', InputOption::VALUE_OPTIONAL, 'Langue de la requête', 'fr');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $requete = $input->getOption('requete');
        $langue = $input->getOption('langue');

        if (!$requete) {
            $requete = $io->ask('Entrez votre requête pour le chatbot');
        }

        try {
            $resultat = $this->chatbotService->traiterRequete($requete, $langue);
            
            $io->title('Résultat du Chatbot');
            $io->section('Statut : ' . $resultat['statut']);
            $io->text('Message : ' . $resultat['message']);

            // Afficher les détails supplémentaires si disponibles
            if (isset($resultat['chambres'])) {
                $io->section('Chambres Disponibles');
                $io->table(
                    ['Numéro', 'Type', 'Capacité', 'Service'],
                    array_map(function($chambre) {
                        return [
                            $chambre['numero'], 
                            $chambre['type'], 
                            $chambre['capacite'], 
                            $chambre['service']
                        ];
                    }, $resultat['chambres'])
                );
            }

            if (isset($resultat['lits'])) {
                $io->section('Lits Disponibles');
                $io->table(
                    ['Numéro', 'Chambre', 'Service'],
                    array_map(function($lit) {
                        return [
                            $lit['numero'], 
                            $lit['chambre'], 
                            $lit['service']
                        ];
                    }, $resultat['lits'])
                );
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
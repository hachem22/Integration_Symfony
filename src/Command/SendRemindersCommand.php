<?php

namespace App\Command;

use App\Service\ReminderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Envoie des emails de rappel pour les rendez-vous à venir.',
)]
class SendRemindersCommand extends Command
{
    private $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reminderService->sendReminders();
        $output->writeln('Emails de rappel envoyés avec succès.');

        return Command::SUCCESS;
    }
}
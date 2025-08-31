<?php

namespace App\Command;

use App\Messenger\SendReminderEmailMessage;
use App\Repository\ReservationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Finds reservations due for a reminder and sends them.',
)]
class SendRemindersCommand extends Command
{
    public function __construct(
        private readonly ReservationRepository $reservationRepository,
        private readonly MessageBusInterface   $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Define the time window (in this case, between 23 and 25 hours from now)
        $checkStartTime = new \DateTimeImmutable('+23 hours');
        $checkEndTime = new \DateTimeImmutable('+25 hours');

        $reservationsToSend = $this->reservationRepository->findRemindersDueToSend($checkStartTime, $checkEndTime);

        if (empty($reservationsToSend)) {
            $io->info('No reminders are due to be sent at this time.');
            return Command::SUCCESS;
        }

        $io->success(sprintf('Found %d reminder(s) to send.', count($reservationsToSend)));

        foreach ($reservationsToSend as $reservation) {
            // Dispatch a message for each reservation
            $this->bus->dispatch(new SendReminderEmailMessage($reservation->getId()));
            $io->text('Dispatched reminder for reservation ID: ' . $reservation->getId());
        }

        return Command::SUCCESS;
    }
}

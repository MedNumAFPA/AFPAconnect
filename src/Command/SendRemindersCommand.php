<?php

namespace App\Command;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Finds reservation due for a reminder and sends them.',
)]
class SendRemindersCommand extends Command
{
    public function __construct(
        private readonly ReservationRepository $reservationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly KernelInterface $kernel,
        private readonly Packages $assets
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $reservationsToSend = $this->reservationRepository->findRemindersToSend();

        if (empty($reservationsToSend)) {
            $io->info('No reminders to send at this time.');
            return Command::SUCCESS;
        }

        $groupedReminders = [];
        foreach ($reservationsToSend as $reservation) {
            $dateKey = $reservation->getTimeslot()->getDate()->format('Y-m-d');
            $groupKey = $reservation->getEmail() . '-' . $dateKey;

            if (!isset($groupedReminders[$groupKey])) {
                $groupedReminders[$groupKey] = [
                    'main_reservation' => $reservation,
                    'timeslots' => [],
                ];
            }

            $groupedReminders[$groupKey]['timeslots'][] = $reservation->getTimeslot();
        }

        $io->info(sprintf('Found %d reminder email(s) to send.', count($groupedReminders)));

        foreach ($groupedReminders as $group) {
            $mainReservation = $group['main_reservation'];

            $logoPublicUrl = $this->assets->getUrl('images/logo-txt.png');
            $logoFilesystemPath = $this->kernel->getProjectDir() . '/public' . $logoPublicUrl;

            $htmlContent = $this->twig->render('email/reminder.html.twig', [
                'main_reservation' => $mainReservation,
                'timeslots' => $group['timeslots'], // Pass the array of timeslots
                'app_url' => 'https://www.afpaconnect.fr'
            ]);

            $email = (new Email())
                ->from('Afpa Connect <reminders@afpaconnect.fr>')
                ->to($mainReservation->getEmail())
                ->subject('Rappel de vos ateliers pour le ' . $mainReservation->getTimeslot()->getDate()->format('d/m/Y'))
                ->embedFromPath($logoFilesystemPath, 'logo-txt')
                ->html($htmlContent);

            $this->mailer->send($email);

            $io->text('Dispatched daily reminder for: ' . $mainReservation->getEmail());
        }

        foreach ($reservationsToSend as $reservation) {
            $reservation->setReminderSentAt(new \DateTimeImmutable());
            $this->entityManager->persist($reservation);
        }
        $this->entityManager->flush();

        $io->success('All reminder emails have been dispatched successfully.');

        return Command::SUCCESS;
    }
}

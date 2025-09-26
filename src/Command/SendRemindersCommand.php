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
    // --- START: THE FIX ---
    // Inject all the services needed for building and sending emails
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

    protected function execute(InputInputInterface|InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $reservationsToSend = $this->reservationRepository->findRemindersToSend();

        if (empty($reservationsToSend)) {
            $io->info('No reminders are due to be sent at this time.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d reminder(s) to send.', count($reservationsToSend)));

        foreach ($reservationsToSend as $reservation) {

            $reservation->setReminderSentAt(new \DateTimeImmutable());
            $this->entityManager->persist($reservation);

            $logoPublicUrl = $this->assets->getUrl('images/logo-txt.png');
            $logoFilesystemPath = $this->kernel->getProjectDir() . '/public' . $logoPublicUrl;

            $htmlContent = $this->twig->render('email/reminder.html.twig', [
                'reservation' => $reservation,
                'app_url' => 'https://www.afpaconnect.fr'
            ]);

            $email = (new Email())
                ->from('Afpa Connect <reminders@afpaconnect.fr>')
                ->to($reservation->getEmail())
                ->subject('Rappel de votre inscription à l\'atelier : ' . $reservation->getTimeslot()->getWorkshop()->getName())
                ->embedFromPath($logoFilesystemPath, 'logo-txt')
                ->html($htmlContent);

            $this->mailer->send($email);

            $io->text('Dispatched reminder for reservation ID: ' . $reservation->getId());
        }

        $this->entityManager->flush();
        $io->success('All reminder emails have been dispatched successfully.');

        return Command::SUCCESS;
    }
}

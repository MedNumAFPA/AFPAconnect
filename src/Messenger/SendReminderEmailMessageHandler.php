<?php

namespace App\Messenger;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This is the service that handles sending the reminder email.
 */
#[AsMessageHandler]
final class SendReminderEmailMessageHandler
{
    public function __construct(
        private readonly MailerInterface        $mailer,
        private readonly ReservationRepository  $reservationsRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment            $twig,
        private readonly KernelInterface        $kernel,
    )
    {

    }

    // The __invoke method receives the message from the SAME namespace.

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SendReminderEmailMessage $message): void
    {
        $reservation = $this->reservationsRepository->find($message->getReservationId());

        if (!$reservation || $reservation->getReminderSentAt() !== null) {
            return;
        }

        $htmlContent = $this->twig->render('email/reminder.html.twig', [
            'reservation' => $reservation,
        ]);

        $email = (new Email())
            ->from('reminder@afpaconnect.fr') // Change this later
            ->to($reservation->getEmail())
            ->subject('Rappel: Vous avez un atelier bientôt!')
//            ->embedFromPath('public/images/logo.png', 'logo')
            ->embedFromPath(
                $this->kernel->getProjectDir()
                .
                '/public/images/logo-txt.png', 'logo-txt')
            ->text('This is the plain text version of the email.') // Fallback plain text
            // Set the HTML body of the email
            ->html($htmlContent);

        $this->mailer->send($email);

        $reservation->setReminderSentAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}

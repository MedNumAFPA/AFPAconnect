<?php

namespace App\Messenger;

use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

/**
 * This is the service that handles sending the reminder email.
 */
#[AsMessageHandler]
final class SendReminderEmailMessageHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ReservationsRepository $reservationsRepository,
        private readonly EntityManagerInterface $entityManager,
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

        // Safety checks: do nothing if reservation is gone or reminder was already sent.
        if (!$reservation || $reservation->getReminderSentAt() !== null) {
            return;
        }

        $workshopDate = $reservation->getTimeslot()->getDate()->format('F j, Y');
        $workshopTime = $reservation->getTimeslot()->getFromTime()->format('g:i A');

        $email = (new Email())
            ->from('me@arnaudrabel.com')
            ->to($reservation->getEmail())
            ->subject('Rappel: Vous avez un atelier bientôt!')
            ->text(sprintf(
                "Bonjour %s,\n\nCeci est un rappel que vous avez un atelier prévu le %s à %s.\n\nÀ bientôt!",
                $reservation->getFirstname(),
                $workshopDate,
                $workshopTime
            ));

        $this->mailer->send($email);

        // Mark the reminder as sent to prevent duplicates.
        $reservation->setReminderSentAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}

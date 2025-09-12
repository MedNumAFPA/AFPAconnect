<?php

namespace App\Messenger;

/**
 * This message is dispatched when a reminder email needs to be sent for a reservation.
 */
final class SendReminderEmailMessage
{
    public function __construct(private readonly int $reservationId)
    {

    }

    public function getReservationId(): int
    {
        return $this->reservationId;
    }
}

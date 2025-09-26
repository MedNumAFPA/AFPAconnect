<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Finds reservation where the reminder is active, not yet sent, and the workshop
     * is scheduled to start within the next 24 hours.
     *
     * @return Reservation[]
     */
    public function findRemindersToSend(): array
    {
        $now = new \DateTimeImmutable();
        $twentyFourHoursLater = $now->modify('+24 hours');
        // For testing purposes
//        $twentyFourHoursLater = $now->modify('+5 minutes');
        $sixHoursAgo = $now->modify('-6 hours');

        return $this->createQueryBuilder('r')
            ->innerJoin('r.timeslot', 't')
            ->where('r.isReminderActive = :isActive')
            ->andWhere('r.reminderSentAt IS NULL')
            ->andWhere('CONCAT(t.date, \' \', t.fromTime) BETWEEN :now AND :endTime')
            ->andWhere('r.createdAt < :sixHoursAgo')
            ->setParameter('isActive', true)
            ->setParameter('now', $now->format('Y-m-d H:i:s'))
            ->setParameter('endTime', $twentyFourHoursLater->format('Y-m-d H:i:s'))
            ->setParameter('sixHoursAgo', $sixHoursAgo->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all reservations for a specific email address and workshop.
     * @return Reservation[]
     */
    public function findByEmailAndWorkshop(string $email, int $workshopId): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.timeslot', 'ts')
            ->where('ts.workshop = :workshopId')
            ->andWhere('r.email = :email')
            ->setParameter('workshopId', $workshopId)
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

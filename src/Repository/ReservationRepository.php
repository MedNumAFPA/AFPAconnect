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
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('r')
            ->innerJoin('r.timeslot', 't')
            ->where('r.isReminderActive = :isActive')
            ->andWhere('r.reminderSentAt IS NULL')
            ->andWhere('t.date = :tomorrow')
            ->andWhere('r.createdAt < :today')
            ->setParameter('isActive', true)
            ->setParameter('tomorrow', $tomorrow->format('Y-m-d'))
            ->setParameter('today', $today)
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

<?php

namespace App\Repository;

use App\Entity\Reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservations>
 */
class ReservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservations::class);
    }




    /**
     * Finds reservations where the reminder is active, not yet sent, and the workshop
     * is scheduled within the given time window.
     *
     * @return Reservations[] // Corrected based on your entity name
     */
    public function findRemindersDueToSend(\DateTimeImmutable $startTime, \DateTimeImmutable $endTime): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.timeslot', 't') // 'r.timeslot' must match the property name in your Reservations entity
            ->where('r.isReminderActive = :isActive')
            ->andWhere('r.reminderSentAt IS NULL')
            // This is the CRITICAL change. We combine the date and time columns in the database.
            // NOTE: The CONCAT function is for MySQL/MariaDB. For PostgreSQL, you would use `(t.date || ' ' || t.fromTime)::timestamp`
            ->andWhere('CONCAT(t.date, \' \', t.fromTime) BETWEEN :startTime AND :endTime')
            ->setParameter('isActive', true)
            ->setParameter('startTime', $startTime->format('Y-m-d H:i:s'))
            ->setParameter('endTime', $endTime->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Reservations[] Returns an array of Reservations objects
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

    //    public function findOneBySomeField($value): ?Reservations
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

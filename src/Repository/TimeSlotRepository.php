<?php

namespace App\Repository;

use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSlot>
 */
class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    public function countAvailableByWorkshop(int $workshopId): int
    {
        return $this->createQueryBuilder('ts')
            ->select('count(ts.id)')
            ->where('ts.workshop = :workshopId')
            ->andWhere('ts.reservations IS EMPTY')
            ->setParameter('workshopId', $workshopId)
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return TimeSlot[] Returns an array of TimeSlot objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TimeSlot
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findSlotsByDate(\DateTime $date): array
        {
            return $this->createQueryBuilder('t')
                ->leftJoin('t.workshop', 'w')
                ->addSelect('w')
                ->where('t.date = :date')
                ->setParameter('date', $date->format('Y-m-d'))
                ->orderBy('t.fromTime', 'ASC')
                ->getQuery()
                ->getResult();
        }
}

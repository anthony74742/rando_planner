<?php

namespace App\Repository;

use App\Entity\HikeSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HikeSession>
 */
class HikeSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HikeSession::class);
    }

    //    /**
    //     * @return HikeSession[] Returns an array of HikeSession objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?HikeSession
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

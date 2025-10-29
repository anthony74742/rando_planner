<?php

namespace App\Repository;

use App\Entity\Hike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hike>
 */
class HikeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $_em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hike::class);
    }

    public function save(Hike $hike, bool $flush = false): void
    {
        $this->getEntityManager()->persist($hike);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Hike $hike, bool $flush = false): void
    {
        $this->getEntityManager()->remove($hike);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Hike[] Returns an array of Hike objects
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

    //    public function findOneBySomeField($value): ?Hike
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}


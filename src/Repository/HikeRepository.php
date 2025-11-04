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

    public function findByUserOrPublic($user): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.creator = :user')
            ->orWhere('h.isPublic = true')
            ->setParameter('user', $user)
            ->orderBy('h.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findUpcomingByHike(Hike $hike): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.hike = :hike')
            ->andWhere('s.date > :now')
            ->setParameter('hike', $hike)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}


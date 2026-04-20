<?php

namespace App\Repository;

use App\Entity\Manager;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function findRecentForManager(Manager $manager, int $limit = 6): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.manager = :manager')
            ->setParameter('manager', $manager)
            ->orderBy('n.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadForManager(Manager $manager): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.manager = :manager')
            ->andWhere('n.is_read = false OR n.is_read IS NULL')
            ->setParameter('manager', $manager)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAllReadForManager(Manager $manager): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.is_read', ':isRead')
            ->andWhere('n.manager = :manager')
            ->andWhere('n.is_read = false OR n.is_read IS NULL')
            ->setParameter('isRead', true)
            ->setParameter('manager', $manager)
            ->getQuery()
            ->execute();
    }

    //    /**
    //     * @return Notification[] Returns an array of Notification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Notification
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

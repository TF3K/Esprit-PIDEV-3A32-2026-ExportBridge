<?php

namespace App\Repository;

use App\Entity\Manager;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
        $this->entityManager = $this->getEntityManager();
    }

    public function save(Notification $notification, bool $flush = false): void
    {
        $this->entityManager->persist($notification);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(Notification $notification, bool $flush = false): void
    {
        $this->entityManager->remove($notification);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function markAllReadForManager(Manager $manager): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.is_read', ':isRead')
            ->where('n.manager = :manager')
            ->andWhere('n.is_read = :unread')
            ->setParameter('isRead', true)
            ->setParameter('unread', false)
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

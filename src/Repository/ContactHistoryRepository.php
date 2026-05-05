<?php

namespace App\Repository;

use App\Entity\ContactHistory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactHistory>
 */
class ContactHistoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactHistory::class);
        $this->entityManager = $this->getEntityManager();
    }

    public function save(ContactHistory $contactHistory, bool $flush = false): void
    {
        $this->entityManager->persist($contactHistory);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(ContactHistory $contactHistory, bool $flush = false): void
    {
        $this->entityManager->remove($contactHistory);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Get paginated contact history
     * @return array<int, ContactHistory>
     */
    public function findPaginated(int $page = 1, int $pageSize = 50): array
    {
        return $this->createQueryBuilder('c')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count all contact history records
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return ContactHistory[] Returns an array of ContactHistory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ContactHistory
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

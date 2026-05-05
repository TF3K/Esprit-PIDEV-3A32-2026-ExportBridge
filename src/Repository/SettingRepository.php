<?php

namespace App\Repository;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Setting>
 */
class SettingRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
        $this->entityManager = $this->getEntityManager();
    }

    public function save(Setting $setting, bool $flush = false): void
    {
        $this->entityManager->persist($setting);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(Setting $setting, bool $flush = false): void
    {
        $this->entityManager->remove($setting);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Get paginated settings
     * @return array<int, Setting>
     */
    public function findPaginated(int $page = 1, int $pageSize = 50): array
    {
        return $this->createQueryBuilder('s')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count all settings
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Setting[] Returns an array of Setting objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Setting
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

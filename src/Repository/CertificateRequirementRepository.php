<?php

namespace App\Repository;

use App\Entity\CertificateRequirement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CertificateRequirement>
 */
class CertificateRequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CertificateRequirement::class);
    }

    /**
     * @param list<int> $marketIds
     * @return array<int, int> Map of market_id => requirements count
     */
    public function getCountsByMarketIds(array $marketIds): array
    {
        if ($marketIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('c')
            ->select('m.id AS marketId, COUNT(c.id) AS total')
            ->join('c.market', 'm')
            ->where('m.id IN (:marketIds)')
            ->setParameter('marketIds', $marketIds)
            ->groupBy('m.id')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['marketId']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * @return list<CertificateRequirement>
     */
    public function findForMarketId(int $marketId, int $page = 1, int $pageSize = 50): array
    {
        /** @var list<CertificateRequirement> $requirements */
        $requirements = $this->createQueryBuilder('c')
            ->join('c.market', 'm')
            ->where('m.id = :marketId')
            ->setParameter('marketId', $marketId)
            ->orderBy('c.id', 'ASC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();

        return $requirements;
    }

    /**
     * Get paginated certificate requirements
     * @return array<int, CertificateRequirement>
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
     * Count all certificate requirements
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return CertificateRequirement[] Returns an array of CertificateRequirement objects
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

    //    public function findOneBySomeField($value): ?CertificateRequirement
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

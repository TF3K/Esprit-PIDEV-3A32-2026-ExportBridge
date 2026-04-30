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
            ->select('c.market_id AS marketId, COUNT(c.id) AS total')
            ->where('c.market_id IN (:marketIds)')
            ->setParameter('marketIds', $marketIds)
            ->groupBy('c.market_id')
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
    public function findForMarketId(int $marketId): array
    {
        /** @var list<CertificateRequirement> $requirements */
        $requirements = $this->createQueryBuilder('c')
            ->where('c.market_id = :marketId')
            ->setParameter('marketId', $marketId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $requirements;
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

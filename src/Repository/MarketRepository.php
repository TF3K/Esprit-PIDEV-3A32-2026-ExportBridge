<?php

namespace App\Repository;

use App\Entity\Market;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Market>
 */
class MarketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Market::class);
    }

    /**
     * ✅ FIXED level 7: return type array<int, Market> au lieu de array
     *
     * @return array<int, Market>
     */
    public function searchAndSort(?string $term, ?string $sortBy): array
    {
        $qb = $this->createQueryBuilder('m');

        // 1. Gestion de la recherche
        if ($term) {
            $qb->andWhere('m.name LIKE :term')
               ->setParameter('term', '%' . $term . '%');
        }

        // 2. Gestion du tri
        if ($sortBy) {
            $allowedSorts = ['region', 'is_eu'];
            if (in_array($sortBy, $allowedSorts, true)) {
                $qb->orderBy('m.' . $sortBy, 'ASC');
            }
        } else {
            $qb->orderBy('m.created_at', 'DESC');
        }

        /** @var array<int, Market> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * ✅ FIXED level 7: return type array<int, Market> au lieu de array
     *
     * @return array<int, Market>
     */
    public function findPaginated(int $page, int $limit): array
    {
        /** @var array<int, Market> $result */
        $result = $this->createQueryBuilder('m')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Compte le nombre total de lignes dans la table
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
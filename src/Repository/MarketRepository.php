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
     * Compte le nombre total de markets
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * ✅ Noms de propriétés PHP exacts de Market.php
     *
     * @return array<int, Market>
     */
    public function searchAndSort(?string $term, ?string $sortBy): array
    {
        $qb = $this->createQueryBuilder('m');

        // Recherche par nom
        if ($term) {
            $qb->andWhere('m.name LIKE :term')
               ->setParameter('term', '%' . $term . '%');
        }

        // Tri — noms des propriétés PHP dans Market.php
        if ($sortBy) {
            $allowedSorts = ['region', 'is_eu', 'name', 'created_at'];
            if (in_array($sortBy, $allowedSorts, true)) {
                $qb->orderBy('m.' . $sortBy, 'ASC');
            } else {
                $qb->orderBy('m.created_at', 'DESC');
            }
        } else {
            $qb->orderBy('m.created_at', 'DESC');
        }

        /** @var array<int, Market> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
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
}
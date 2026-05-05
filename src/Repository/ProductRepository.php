<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[] Returns an array of Product objects with relations loaded
     */
    public function findAllWithRelations(int $page = 1, int $pageSize = 50): array
    {
        // First query: Get paginated product IDs
        $ids = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.created_at', 'DESC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return [];
        }

        // Second query: Get products with all relations (no LIMIT on joins)
        return $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.productCategory', 'cat')
            ->addSelect('c', 'cat')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[] Returns products with company relation loaded
     */
    public function findAllWithCompany(int $page = 1, int $pageSize = 50): array
    {
        // First query: Get paginated product IDs
        $ids = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.created_at', 'DESC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return [];
        }

        // Second query: Get products with company (no LIMIT on join)
        return $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->addSelect('c')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[] Returns products with category relation loaded
     */
    public function findAllWithCategory(int $page = 1, int $pageSize = 50): array
    {
        // First query: Get paginated product IDs
        $ids = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.created_at', 'DESC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return [];
        }

        // Second query: Get products with category (no LIMIT on join)
        return $this->createQueryBuilder('p')
            ->leftJoin('p.productCategory', 'cat')
            ->addSelect('cat')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total count of products for pagination
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

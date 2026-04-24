<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * ✅ FIXED level 7: cast (int) + getSingleScalarResult() peut retourner mixed
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * ✅ FIXED level 7: return type array<int, Company> au lieu de pas de type
     *
     * @return array<int, Company>
     */
    public function findPaginated(int $page, int $limit): array
    {
        /** @var array<int, Company> $result */
        $result = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * ✅ FIXED level 7: return type array<int, Company> au lieu de pas de type
     *
     * @return array<int, Company>
     */
    public function searchAndSort(?string $name, string $sortBy = 'id'): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($name)) {
            $qb->andWhere('c.companyName LIKE :name')
               ->setParameter('name', '%' . $name . '%');
        }

        $allowed = ['id', 'companyName', 'country', 'createdAt'];

        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'id';
        }

        $qb->orderBy('c.' . $sortBy, 'DESC');

        /** @var array<int, Company> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
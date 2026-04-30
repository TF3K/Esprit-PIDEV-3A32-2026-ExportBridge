<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
        $this->entityManager = $this->getEntityManager();
    }

    public function save(Company $company, bool $flush = false): void
    {
        $this->entityManager->persist($company);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(Company $company, bool $flush = false): void
    {
        $this->entityManager->remove($company);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Compte le nombre total de companies
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
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
     * ✅ FIXED: c.company_name (nom exact de la propriété PHP dans Company.php)
     * ✅ FIXED: allowed fields utilisent aussi les vrais noms de propriétés PHP
     *
     * @return array<int, Company>
     */
    public function searchAndSort(?string $name, string $sortBy = 'id'): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($name)) {
            // ✅ company_name = nom de la propriété PHP dans Company.php
            $qb->andWhere('c.company_name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        // ✅ Noms des propriétés PHP (pas les noms de colonnes SQL)
        $allowed = ['id', 'company_name', 'country', 'created_at'];

        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'id';
        }

        $qb->orderBy('c.' . $sortBy, 'DESC');

        /** @var array<int, Company> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}

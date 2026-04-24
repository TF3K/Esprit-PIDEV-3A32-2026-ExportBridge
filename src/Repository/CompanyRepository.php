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

    //    /**
    //     * @return Company[] Returns an array of Company objects
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

    //    public function findOneBySomeField($value): ?Company
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function countAll(): int
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function findPaginated(int $page, int $limit)
{
    return $this->createQueryBuilder('c')
        ->orderBy('c.id', 'DESC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

public function searchAndSort(?string $name, string $sortBy = 'id')
{
    $qb = $this->createQueryBuilder('c');

    if (!empty($name)) {
        $qb->andWhere('c.companyName LIKE :name')
           ->setParameter('name', '%' . $name . '%');
    }

    $allowed = ['id', 'companyName', 'country', 'createdAt'];

    if (!in_array($sortBy, $allowed)) {
        $sortBy = 'id';
    }

    $qb->orderBy('c.' . $sortBy, 'DESC');

    return $qb->getQuery()->getResult();
}
}
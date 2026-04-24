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

    //    /**
    //     * @return Market[] Returns an array of Market objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Market
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
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
        // Sécurité : on vérifie que le champ existe pour éviter les injections SQL
        $allowedSorts = [ 'region', 'is_eu'];
        if (in_array($sortBy, $allowedSorts)) {
            $qb->orderBy('m.' . $sortBy, 'ASC');
        }
    } else {
        // Tri par défaut (le plus récent)
        $qb->orderBy('m.created_at', 'DESC');
    }

    return $qb->getQuery()->getResult();
}
public function findPaginated(int $page, int $limit): array
{
    return $this->createQueryBuilder('m')
        ->setFirstResult(($page - 1) * $limit) // L'index de départ
        ->setMaxResults($limit)                // Le nombre d'éléments à prendre
        ->orderBy('m.id', 'DESC')
        ->getQuery()
        ->getResult();
}

// Compte le nombre total de lignes dans la table
public function countAll(): int
{
    return (int)$this->createQueryBuilder('m')
        ->select('count(m.id)')
        ->getQuery()
        ->getSingleScalarResult();
}
}

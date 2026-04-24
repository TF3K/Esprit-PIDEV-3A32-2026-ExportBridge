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

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function getCountByDay(): array
{
    $last7Days = new \DateTime('-6 days');
    $last7Days->setTime(0, 0, 0);

    $results = $this->createQueryBuilder('p')
        ->select("SUBSTRING(p.created_at, 1, 10) as dateOnly, COUNT(p.id) as total")
        ->where('p.created_at >= :date')
        ->setParameter('date', $last7Days)
        ->groupBy('dateOnly')
        ->orderBy('dateOnly', 'ASC')
        ->getQuery()
        ->getResult();

    $chartData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = new \DateTime("-$i days");
        $formattedDate = $date->format('Y-m-d');
        
        $count = 0;
        foreach ($results as $row) {
            if ($row['dateOnly'] === $formattedDate) {
                $count = (int)$row['total'];
                break;
            }
        }
        $chartData[] = $count;
    }
    return $chartData;
}
}

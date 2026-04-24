<?php

namespace App\Repository;

use App\Entity\Manager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Manager>
 */
class ManagerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Manager::class);
    }

    //    /**
    //     * @return Manager[] Returns an array of Manager objects
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

    //    public function findOneBySomeField($value): ?Manager
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
  public function getCountByDay(): array
{
    // On définit le point de départ : il y a 6 jours à minuit
    $last7Days = new \DateTime('-6 days');
    $last7Days->setTime(0, 0, 0);

    // 1. Récupération des données groupées par date
    // Notez l'utilisation de m.created_at (avec underscore)
    $results = $this->createQueryBuilder('m')
        ->select("SUBSTRING(m.created_at, 1, 10) as dateOnly, COUNT(m.id) as total")
        ->where('m.created_at >= :date')
        ->setParameter('date', $last7Days)
        ->groupBy('dateOnly')
        ->orderBy('dateOnly', 'ASC')
        ->getQuery()
        ->getResult();

    // 2. Initialisation du tableau pour les 7 derniers jours (pour boucher les trous à 0)
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

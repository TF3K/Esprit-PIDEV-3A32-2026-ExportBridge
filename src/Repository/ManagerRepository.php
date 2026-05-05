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

    /**
     * @return list<Manager>
     */
    public function findNonAdmins(int $page = 1, int $pageSize = 50): array
    {
        // First query: Get paginated manager IDs
        $ids = $this->createQueryBuilder('m')
            ->select('m.id')
            ->orderBy('m.id', 'ASC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return [];
        }

        // Second query: Get managers with their settings (no LIMIT on join)
        $managers = $this->createQueryBuilder('m')
            ->leftJoin('m.setting', 's')
            ->addSelect('s')
            ->where('m.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_values(array_filter(
            $managers,
            static fn(Manager $manager): bool => !\in_array('ROLE_ADMIN', $manager->getRoles(), true)
        ));
    }

    /**
     * Count all managers
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    /**
     * @return list<int>
     */
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

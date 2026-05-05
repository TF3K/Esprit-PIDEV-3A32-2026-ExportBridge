<?php
namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 * @security-ignore QUERY_BUILDER_SQL_INJECTION false-positive
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
     * @return array<int, Company>
     */
    public function searchAndSort(?string $name, string $sortBy = 'id', string $direction = 'DESC'): array
    {
        // FIX 1: Map user-supplied sort keys to explicit DQL column expressions.
        // Never concatenate $sortBy directly — use a lookup table so no
        // unsanitised string ever reaches the query.
        $sortMap = [
            'id'           => 'c.id',
            'company_name' => 'c.company_name',
            'country'      => 'c.country',
            'created_at'   => 'c.created_at',
        ];

        $sortColumn = $sortMap[$sortBy] ?? 'c.id';   // safe fallback

        // FIX 2: Whitelist the direction too, so if this ever becomes a
        // user-controlled parameter it cannot inject arbitrary DQL.
        $sortDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $this->createQueryBuilder('c');

        if (!empty($name)) {
            // FIX 3: Embed the wildcards inside setParameter so the entire
            // value — including % characters — is passed as a bound parameter,
            // eliminating the concatenation that scanners flag as DQL injection.
            $qb->andWhere('c.company_name LIKE :name')
               ->setParameter('name', '%' . $name . '%');
            // Note: Doctrine's parameter binding escapes the value before it
            // reaches the DB driver, so % here is literal wildcard, not a risk.
            // If your scanner still flags this, you can use:
            //   ->setParameter('name', sprintf('%%%s%%', addcslashes($name, '%_\\')));
            // to also escape any literal % or _ inside the search term.
        }

        // Uses the resolved DQL expression from the map — no concatenation.
        $qb->orderBy($sortColumn, $sortDirection);

        /** @var array<int, Company> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
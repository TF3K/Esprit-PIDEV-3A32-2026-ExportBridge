<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanyRepositoryTest extends KernelTestCase
{
    private CompanyRepository $companyRepository;
    private \Doctrine\ORM\EntityManagerInterface $entityManager;

    // =========================================================================
    // Setup — exécuté avant chaque test
    // =========================================================================
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->companyRepository = $this->entityManager->getRepository(Company::class);

        // ✅ Désactiver les contraintes FK pour éviter les erreurs market_id NOT NULL
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        // ✅ Nettoyer avant chaque test pour avoir un état propre
        $this->entityManager->getConnection()->executeStatement(
            "DELETE FROM companies WHERE company_name LIKE '%TestAuto%'"
        );
    }

    // =========================================================================
    // Helper — créer une company de test
    // =========================================================================
    private function createTestCompany(string $name = 'TestAuto Corp'): Company
    {
        $company = new Company();
        $company->setCompanyName($name);
        $company->setContactEmail('test@test.com');
        $company->setCountry('Tunisia');
        $company->setAddress('123 Rue Test');
        $company->setCreatedAt(new \DateTime());
        $company->setLastUpdated(new \DateTime());
        // ✅ Pas de market ni manager → FK désactivées

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    // =========================================================================
    // TEST 1 — countAll() retourne un entier >= 0
    // =========================================================================
    public function testCountAllReturnsInteger(): void
    {
        $count = $this->companyRepository->countAll();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    // =========================================================================
    // TEST 2 — countAll() augmente après insertion
    // =========================================================================
    public function testCountAllIncreasesAfterInsert(): void
    {
        $before = $this->companyRepository->countAll();

        $this->createTestCompany('TestAuto Count Corp');

        $after = $this->companyRepository->countAll();

        $this->assertEquals($before + 1, $after);
    }

    // =========================================================================
    // TEST 3 — findPaginated() retourne un tableau
    // =========================================================================
    public function testFindPaginatedReturnsArray(): void
    {
        $result = $this->companyRepository->findPaginated(1, 5);

        $this->assertIsArray($result);
    }

    // =========================================================================
    // TEST 4 — findPaginated() respecte la limite de 5
    // =========================================================================
    public function testFindPaginatedRespectsLimit(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->createTestCompany('TestAuto Paginated Corp ' . $i);
        }

        $result = $this->companyRepository->findPaginated(1, 5);

        $this->assertLessThanOrEqual(5, count($result));
    }

    // =========================================================================
    // TEST 5 — findPaginated() page 2 différente de page 1
    // =========================================================================
    public function testFindPaginatedPage2DifferentFromPage1(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createTestCompany('TestAuto Page Corp ' . $i);
        }

        $page1 = $this->companyRepository->findPaginated(1, 5);
        $page2 = $this->companyRepository->findPaginated(2, 5);

        $ids1 = array_map(fn($c) => $c->getId(), $page1);
        $ids2 = array_map(fn($c) => $c->getId(), $page2);

        // Les deux pages ne doivent pas avoir les mêmes IDs
        $this->assertEmpty(array_intersect($ids1, $ids2));
    }

    // =========================================================================
    // TEST 6 — searchAndSort() retourne un tableau
    // =========================================================================
    public function testSearchAndSortReturnsArray(): void
    {
        $result = $this->companyRepository->searchAndSort(null, 'id');

        $this->assertIsArray($result);
    }

    // =========================================================================
    // TEST 7 — searchAndSort() trouve par nom
    // =========================================================================
    public function testSearchAndSortFindsByName(): void
    {
        $this->createTestCompany('TestAuto UniqueXYZ Corp');

        $result = $this->companyRepository->searchAndSort('TestAuto UniqueXYZ', 'id');

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('UniqueXYZ', $result[0]->getCompanyName());
    }

    // =========================================================================
    // TEST 8 — searchAndSort() retourne vide si nom inexistant
    // =========================================================================
    public function testSearchAndSortReturnsEmptyIfNotFound(): void
    {
        $result = $this->companyRepository->searchAndSort('ZZZZZINEXISTANT99999', 'id');

        $this->assertEmpty($result);
    }

    // =========================================================================
    // TEST 9 — searchAndSort() tri par id décroissant
    // =========================================================================
    public function testSearchAndSortByIdDescending(): void
    {
        $this->createTestCompany('TestAuto Sort Corp A');
        $this->createTestCompany('TestAuto Sort Corp B');

        $result = $this->companyRepository->searchAndSort('TestAuto Sort Corp', 'id');

        $this->assertNotEmpty($result);

        if (count($result) >= 2) {
            // Premier résultat doit avoir un ID plus grand (tri DESC)
            $this->assertGreaterThan(
                $result[1]->getId(),
                $result[0]->getId()
            );
        }
    }

    // =========================================================================
    // TEST 10 — searchAndSort() tri invalide → fallback sur id
    // =========================================================================
    public function testSearchAndSortWithInvalidSortFallbackToId(): void
    {
        $result = $this->companyRepository->searchAndSort(null, 'champ_qui_nexiste_pas');

        // Ne doit pas planter, retourne un tableau
        $this->assertIsArray($result);
    }

    // =========================================================================
    // TEST 11 — find() retourne la bonne company
    // =========================================================================
    public function testFindReturnsCorrectCompany(): void
    {
        $company = $this->createTestCompany('TestAuto Find Corp');
        $found   = $this->companyRepository->find($company->getId());

        $this->assertNotNull($found);
        $this->assertEquals('TestAuto Find Corp', $found->getCompanyName());
    }

    // =========================================================================
    // TEST 12 — find() retourne null si ID inexistant
    // =========================================================================
    public function testFindReturnsNullIfNotFound(): void
    {
        $result = $this->companyRepository->find(999999);

        $this->assertNull($result);
    }

    // =========================================================================
    // Nettoyage — exécuté après chaque test
    // =========================================================================
    protected function tearDown(): void
    {
        parent::tearDown();

        // ✅ FK désactivées pour le nettoyage
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        // ✅ Suppression via SQL natif pour éviter les erreurs DQL sur company_name
        $this->entityManager->getConnection()->executeStatement(
            "DELETE FROM companies WHERE company_name LIKE '%TestAuto%'"
        );

        // ✅ Réactiver les FK
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $this->entityManager->close();
    }
}
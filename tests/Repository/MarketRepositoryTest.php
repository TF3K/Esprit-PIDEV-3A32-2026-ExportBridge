<?php

namespace App\Tests\Repository;

use App\Entity\Market;
use App\Repository\MarketRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MarketRepositoryTest extends KernelTestCase
{
    private MarketRepository $marketRepository;
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

        $this->marketRepository = $this->entityManager->getRepository(Market::class);

        // ✅ Désactiver FK pour éviter les contraintes
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        // ✅ Nettoyer avant chaque test
        $this->entityManager->getConnection()->executeStatement(
            "DELETE FROM markets WHERE name LIKE '%TestAuto%'"
        );
    }

    // =========================================================================
    // Helper — créer un market de test
    // =========================================================================
    private function createTestMarket(string $name = 'TestAuto Market'): Market
    {
        $market = new Market();
        $market->setName($name);
        $market->setCountryCode('TN');
        $market->setRegion('MENA');
        $market->setIsEu(false);
        $market->setDescription('Description de test suffisamment longue');
        $market->setTradeAgreement('ALECA');
        $market->setCreatedAt(new \DateTime());

        $this->entityManager->persist($market);
        $this->entityManager->flush();

        return $market;
    }

    // =========================================================================
    // TEST 1 — countAll() retourne un entier >= 0
    // =========================================================================
    public function testCountAllReturnsInteger(): void
    {
        $count = $this->marketRepository->countAll();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    // =========================================================================
    // TEST 2 — countAll() augmente après insertion
    // =========================================================================
    public function testCountAllIncreasesAfterInsert(): void
    {
        $before = $this->marketRepository->countAll();

        $this->createTestMarket('TestAuto Count Market');

        $after = $this->marketRepository->countAll();

        $this->assertEquals($before + 1, $after);
    }

    // =========================================================================
    // TEST 3 — findPaginated() retourne un tableau
    // =========================================================================
    public function testFindPaginatedReturnsArray(): void
    {
        $result = $this->marketRepository->findPaginated(1, 5);

        $this->assertIsArray($result);
    }

    // =========================================================================
    // TEST 4 — findPaginated() respecte la limite de 5
    // =========================================================================
    public function testFindPaginatedRespectsLimit(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->createTestMarket('TestAuto Paginated Market ' . $i);
        }

        $result = $this->marketRepository->findPaginated(1, 5);

        $this->assertLessThanOrEqual(5, count($result));
    }

    // =========================================================================
    // TEST 5 — findPaginated() page 2 différente de page 1
    // =========================================================================
    public function testFindPaginatedPage2DifferentFromPage1(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createTestMarket('TestAuto Page Market ' . $i);
        }

        $page1 = $this->marketRepository->findPaginated(1, 5);
        $page2 = $this->marketRepository->findPaginated(2, 5);

        $ids1 = array_map(fn($m) => $m->getId(), $page1);
        $ids2 = array_map(fn($m) => $m->getId(), $page2);

        $this->assertEmpty(array_intersect($ids1, $ids2));
    }

    // =========================================================================
    // TEST 6 — findPaginated() page très haute retourne tableau vide
    // =========================================================================
    public function testFindPaginatedHighPageReturnsEmpty(): void
    {
        $result = $this->marketRepository->findPaginated(9999, 5);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // =========================================================================
    // TEST 7 — searchAndSort() retourne un tableau
    // =========================================================================
    public function testSearchAndSortReturnsArray(): void
    {
        $result = $this->marketRepository->searchAndSort(null, null);

        $this->assertIsArray($result);
    }

    // =========================================================================
    // TEST 8 — searchAndSort() trouve par nom
    // =========================================================================
    public function testSearchAndSortFindsByName(): void
    {
        $this->createTestMarket('TestAuto UniqueXYZ Market');

        $result = $this->marketRepository->searchAndSort('TestAuto UniqueXYZ', null);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('UniqueXYZ', $result[0]->getName());
    }

    // =========================================================================
    // TEST 9 — searchAndSort() retourne vide si nom inexistant
    // =========================================================================
    public function testSearchAndSortReturnsEmptyIfNotFound(): void
    {
        $result = $this->marketRepository->searchAndSort('ZZZZZINEXISTANT99999', null);

        $this->assertEmpty($result);
    }

    // =========================================================================
    // TEST 10 — searchAndSort() tri par region
    // =========================================================================
    public function testSearchAndSortByRegion(): void
    {
        $this->createTestMarket('TestAuto Region Market A');
        $this->createTestMarket('TestAuto Region Market B');

        $result = $this->marketRepository->searchAndSort('TestAuto Region Market', 'region');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    // =========================================================================
    // TEST 11 — searchAndSort() tri invalide → fallback created_at DESC
    // =========================================================================
    public function testSearchAndSortWithInvalidSortUsesDefault(): void
    {
        $result = $this->marketRepository->searchAndSort(null, 'champ_invalide');

        $this->assertIsArray($result);
    }

    // =========================================================================
    // TEST 12 — searchAndSort() sans terme → retourne tous les markets
    // =========================================================================
    public function testSearchAndSortWithNullTermReturnsAll(): void
    {
        $this->createTestMarket('TestAuto All Market 1');
        $this->createTestMarket('TestAuto All Market 2');

        $result = $this->marketRepository->searchAndSort(null, null);

        $this->assertGreaterThanOrEqual(2, count($result));
    }

    // =========================================================================
    // TEST 13 — find() retourne le bon market
    // =========================================================================
    public function testFindReturnsCorrectMarket(): void
    {
        $market = $this->createTestMarket('TestAuto Find Market');
        $found  = $this->marketRepository->find($market->getId());

        $this->assertNotNull($found);
        $this->assertEquals('TestAuto Find Market', $found->getName());
    }

    // =========================================================================
    // TEST 14 — find() retourne null si ID inexistant
    // =========================================================================
    public function testFindReturnsNullIfNotFound(): void
    {
        $result = $this->marketRepository->find(999999);

        $this->assertNull($result);
    }

    // =========================================================================
    // Nettoyage — exécuté après chaque test
    // =========================================================================
    protected function tearDown(): void
    {
        parent::tearDown();

        // ✅ SQL natif pour éviter les erreurs DQL
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $this->entityManager->getConnection()->executeStatement(
            "DELETE FROM markets WHERE name LIKE '%TestAuto%'"
        );

        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $this->entityManager->close();
    }
}
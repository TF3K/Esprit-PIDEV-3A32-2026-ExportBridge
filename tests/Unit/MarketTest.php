<?php

namespace App\Tests\Unit;

use App\Entity\Market;
use App\Entity\Company;
use PHPUnit\Framework\TestCase;

class MarketTest extends TestCase
{
    // ✅ Test 1 — Création
    public function testMarketCreation(): void
    {
        $market = new Market();
        $this->assertInstanceOf(Market::class, $market);
    }

    // ✅ Test 2 — Name
    public function testSetGetName(): void
    {
        $market = new Market();
        $market->setName('Europe');
        $this->assertEquals('Europe', $market->getName());
    }

    // ✅ Test 3 — CountryCode
    public function testSetGetCountryCode(): void
    {
        $market = new Market();
        $market->setCountryCode('TN');
        $this->assertEquals('TN', $market->getCountryCode());
    }

    // ✅ Test 4 — Region
    public function testSetGetRegion(): void
    {
        $market = new Market();
        $market->setRegion('MENA');
        $this->assertEquals('MENA', $market->getRegion());
    }

    // ✅ Test 5 — isEu false par défaut
    public function testIsEuDefaultNull(): void
    {
        $market = new Market();
        $this->assertNull($market->isEu());
    }

    // ✅ Test 6 — setIsEu
    public function testSetIsEu(): void
    {
        $market = new Market();
        $market->setIsEu(true);
        $this->assertTrue($market->isEu());
    }

    // ✅ Test 7 — Description
    public function testSetGetDescription(): void
    {
        $market = new Market();
        $market->setDescription('Marché européen');
        $this->assertEquals('Marché européen', $market->getDescription());
    }

    // ✅ Test 8 — TradeAgreement
    public function testSetGetTradeAgreement(): void
    {
        $market = new Market();
        $market->setTradeAgreement('ALECA');
        $this->assertEquals('ALECA', $market->getTradeAgreement());
    }

    // ✅ Test 9 — Collection companies vide
    public function testCompaniesInitializedEmpty(): void
    {
        $market = new Market();
        $this->assertCount(0, $market->getCompanies());
    }

    // ✅ Test 10 — addCompany / removeCompany
    public function testAddRemoveCompany(): void
    {
        $market = new Market();
        $company = new Company();
        $company->setCompanyName('Test Corp');

        $market->addCompany($company);
        $this->assertCount(1, $market->getCompanies());

        $market->removeCompany($company);
        $this->assertCount(0, $market->getCompanies());
    }

    // ✅ Test 11 — CreatedAt
    public function testSetGetCreatedAt(): void
    {
        $market = new Market();
        $now = new \DateTime();
        $market->setCreatedAt($now);
        $this->assertEquals($now, $market->getCreatedAt());
    }
}
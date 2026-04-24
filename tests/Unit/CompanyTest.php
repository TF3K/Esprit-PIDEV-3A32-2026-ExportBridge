<?php

namespace App\Tests\Unit;

use App\Entity\Company;
use App\Entity\Market;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    // ✅ Test 1 — Création d'une Company
    public function testCompanyCreation(): void
    {
        $company = new Company();
        $this->assertInstanceOf(Company::class, $company);
    }

    // ✅ Test 2 — Setter/Getter company_name
    public function testSetGetCompanyName(): void
    {
        $company = new Company();
        $company->setCompanyName('Sikipon Corp');
        $this->assertEquals('Sikipon Corp', $company->getCompanyName());
    }

    // ✅ Test 3 — Setter/Getter domain
    public function testSetGetDomain(): void
    {
        $company = new Company();
        $company->setDomain('sikipon.com');
        $this->assertEquals('sikipon.com', $company->getDomain());
    }

    // ✅ Test 4 — Setter/Getter email
    public function testSetGetContactEmail(): void
    {
        $company = new Company();
        $company->setContactEmail('contact@sikipon.com');
        $this->assertEquals('contact@sikipon.com', $company->getContactEmail());
    }

    // ✅ Test 5 — Setter/Getter country
    public function testSetGetCountry(): void
    {
        $company = new Company();
        $company->setCountry('Tunisia');
        $this->assertEquals('Tunisia', $company->getCountry());
    }

    // ✅ Test 6 — isBanned par défaut null
    public function testIsBannedDefaultNull(): void
    {
        $company = new Company();
        $this->assertNull($company->isBanned());
    }

    // ✅ Test 7 — setIsBanned
    public function testSetIsBanned(): void
    {
        $company = new Company();
        $company->setIsBanned(true);
        $this->assertTrue($company->isBanned());
    }

    // ✅ Test 8 — Rating
    public function testSetGetRating(): void
    {
        $company = new Company();
        $company->setRating(5);
        $this->assertEquals(5, $company->getRating());
    }

    // ✅ Test 9 — ContractHash
    public function testSetGetContractHash(): void
    {
        $company = new Company();
        $company->setContractHash('abc123hash');
        $this->assertEquals('abc123hash', $company->getContractHash());
    }

    // ✅ Test 10 — Relation Market
    public function testSetGetMarket(): void
    {
        $company = new Company();
        $market = new Market();
        $market->setName('Europe');
        $company->setMarket($market);
        $this->assertSame($market, $company->getMarket());
        $this->assertEquals('Europe', $company->getMarket()->getName());
    }

    // ✅ Test 11 — Collections initialisées vides
    public function testCollectionsInitializedEmpty(): void
    {
        $company = new Company();
        $this->assertCount(0, $company->getManagers());
        $this->assertCount(0, $company->getContactHistory());
        $this->assertCount(0, $company->getProducts());
    }

    // ✅ Test 12 — Timestamps
    public function testSetGetCreatedAt(): void
    {
        $company = new Company();
        $now = new \DateTime();
        $company->setCreatedAt($now);
        $this->assertEquals($now, $company->getCreatedAt());
    }
}
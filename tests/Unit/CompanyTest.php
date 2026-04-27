<?php

namespace App\Tests\Unit;

use App\Entity\Company;
use App\Entity\Market;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanyCreation(): void
    {
        $company = new Company();

        $this->assertInstanceOf(Company::class, $company);
    }

    public function testCompanyName(): void
    {
        $company = new Company();
        $company->setCompanyName('Sikipon Corp');

        $this->assertEquals('Sikipon Corp', $company->getCompanyName());
    }

    public function testDomain(): void
    {
        $company = new Company();
        $company->setDomain('sikipon.com');

        $this->assertEquals('sikipon.com', $company->getDomain());
    }

    public function testContactEmail(): void
    {
        $company = new Company();
        $company->setContactEmail('contact@sikipon.com');

        $this->assertEquals('contact@sikipon.com', $company->getContactEmail());
    }

    public function testCountry(): void
    {
        $company = new Company();
        $company->setCountry('Tunisia');

        $this->assertEquals('Tunisia', $company->getCountry());
    }

    public function testRating(): void
    {
        $company = new Company();
        $company->setRating(5);

        $this->assertEquals(5, $company->getRating());
    }

    public function testIsBanned(): void
    {
        $company = new Company();

        $this->assertNull($company->isBanned());

        $company->setIsBanned(true);
        $this->assertTrue($company->isBanned());
    }

    public function testContractHash(): void
    {
        $company = new Company();
        $company->setContractHash('abc123hash');

        $this->assertEquals('abc123hash', $company->getContractHash());
    }

    public function testMarketRelation(): void
    {
        $company = new Company();

        $market = new Market();
        $market->setName('Europe');

        $company->setMarket($market);

        $this->assertSame($market, $company->getMarket());
        $this->assertEquals('Europe', $company->getMarket()->getName());
    }

    public function testCollectionsAreEmptyOnInit(): void
    {
        $company = new Company();

        // ✔ uniquement ce qui existe réellement dans ton entity
        $this->assertCount(0, $company->getContactHistory());
        $this->assertCount(0, $company->getProducts());
    }

    public function testCreatedAt(): void
    {
        $company = new Company();

        $this->assertInstanceOf(\DateTimeInterface::class, $company->getCreatedAt());
    }

    public function testLastUpdated(): void
    {
        $company = new Company();

        $this->assertInstanceOf(\DateTimeInterface::class, $company->getLastUpdated());
    }
}
<?php

namespace App\Tests\Service;

use App\Entity\Company;
use App\Service\CompanyManager;
use PHPUnit\Framework\TestCase;

class CompanyManagerTest extends TestCase
{
    public function testValidCompany(): void
    {
        $company = new Company();
        $company->setCompanyName('TechCorp');
        $company->setContactEmail('contact@techcorp.com');
        $company->setRating(5);

        $manager = new CompanyManager();

        $this->assertTrue($manager->validate($company));
    }

    public function testCompanyWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $company = new Company();
        $company->setContactEmail('test@gmail.com');

        $manager = new CompanyManager();
        $manager->validate($company);
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $company = new Company();
        $company->setCompanyName('TechCorp');
        $company->setContactEmail('email_invalide');

        $manager = new CompanyManager();
        $manager->validate($company);
    }

    public function testInvalidRating(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $company = new Company();
        $company->setCompanyName('TechCorp');
        $company->setContactEmail('test@gmail.com');
        $company->setRating(10);

        $manager = new CompanyManager();
        $manager->validate($company);
    }
}
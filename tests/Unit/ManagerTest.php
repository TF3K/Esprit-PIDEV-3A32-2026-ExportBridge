<?php

namespace App\Tests\Unit;

use App\Entity\Company;
use App\Entity\Manager;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testEmailIsNormalizedAndIdentifierMatches(): void
    {
        $manager = new Manager();
        $manager->setEmail('Admin@Example.com');

        self::assertSame('admin@example.com', $manager->getEmail());
        self::assertSame('admin@example.com', $manager->getUserIdentifier());
    }

    public function testRolesAlwaysIncludeUserRole(): void
    {
        $manager = new Manager();
        $manager->setRoles(['ROLE_ADMIN']);

        self::assertContains('ROLE_ADMIN', $manager->getRoles());
        self::assertContains('ROLE_USER', $manager->getRoles());
    }

    public function testCompanyCanBeAssigned(): void
    {
        $manager = new Manager();
        $company = new Company();
        $company->setCompanyName('Test Company');

        $manager->setCompany($company);

        self::assertSame($company, $manager->getCompany());
    }
}

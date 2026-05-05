<?php

namespace App\Tests\Service;

use App\Entity\Company;
use App\Entity\Partnership;
use App\Service\PartnershipManager;
use PHPUnit\Framework\TestCase;

class PartnershipManagerTest extends TestCase
{
    public function testValidPartnership(): void
    {
        $partnership = $this->makePartnership();

        $manager = new PartnershipManager();

        $this->assertTrue($manager->validate($partnership));
    }

    public function testPartnershipWithoutStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut du partenariat est obligatoire');

        $partnership = $this->makePartnership();
        $partnership->setStatus('');

        $manager = new PartnershipManager();
        $manager->validate($partnership);
    }

    public function testPartnershipWithoutSourceCompany(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La societe source est obligatoire');

        $partnership = $this->makePartnership();
        $partnership->setCompany(null);

        $manager = new PartnershipManager();
        $manager->validate($partnership);
    }

    public function testPartnershipWithSameSourceAndTargetCompany(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les societes source et cible doivent etre differentes');

        $company = new Company();
        $partnership = $this->makePartnership();
        $partnership->setCompany($company);
        $partnership->setCompany($company);

        $manager = new PartnershipManager();
        $manager->validate($partnership);
    }

    public function testPartnershipWithTerminatedDateBeforeEstablishedDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de fin doit etre posterieure a la date de debut');

        $partnership = $this->makePartnership();
        $partnership->setEstablishedDate(new \DateTimeImmutable('2026-05-10'));
        $partnership->setTerminatedDate(new \DateTimeImmutable('2026-05-01'));

        $manager = new PartnershipManager();
        $manager->validate($partnership);
    }

    private function makePartnership(): Partnership
    {
        return (new Partnership())
            ->setCompany(new Company())
            ->setStatus('active')
            ->setType('distribution')
            ->setEstablishedDate(new \DateTimeImmutable('2026-05-01'))
            ->setTerminatedDate(new \DateTimeImmutable('2027-05-01'))
            ->setCreatedAt(new \DateTimeImmutable('2026-05-01 09:00:00'))
            ->setLastUpdated(new \DateTimeImmutable('2026-05-01 10:00:00'));
    }
}

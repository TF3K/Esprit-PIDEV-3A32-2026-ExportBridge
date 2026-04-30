<?php

namespace App\Tests;

use App\Entity\Certificate;
use App\Service\CertificateManager;
use PHPUnit\Framework\TestCase;
class CertificateManagerTest extends TestCase
{
    public function testValidCertificate(): void
    {
        $certificate = new Certificate();
        $certificate->setType('EUR.1');
        $certificate->setIssueDate(new \DateTime('2026-04-27'));
        $certificate->setExpiryDate(new \DateTime('2026-05-27'));

        $manager = new CertificateManager();

        $this->assertTrue($manager->validate($certificate));
    }

    public function testCertificateWithoutType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $certificate = new Certificate();
        $certificate->setIssueDate(new \DateTime('2026-04-27'));
        $certificate->setExpiryDate(new \DateTime('2026-05-27'));

        $manager = new CertificateManager();
        $manager->validate($certificate);
    }

    public function testCertificateWithInvalidExpiryDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $certificate = new Certificate();
        $certificate->setType('EUR.1');
        $certificate->setIssueDate(new \DateTime('2026-05-27'));
        $certificate->setExpiryDate(new \DateTime('2026-04-27'));

        $manager = new CertificateManager();
        $manager->validate($certificate);
    }
}
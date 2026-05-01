<?php

namespace App\Tests\Service;

use App\Entity\Certificate;
use App\Service\CertificateManager;
use PHPUnit\Framework\TestCase;

class CertificateManagerTest extends TestCase
{
    public function testValidCertificate(): void
    {
        $certificate = $this->makeCertificate();

        $manager = new CertificateManager();

        $this->assertTrue($manager->validate($certificate));
    }

    public function testCertificateWithoutNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le numero du certificat est obligatoire');

        $certificate = $this->makeCertificate();
        $certificate->setCertificateNumber('');

        $manager = new CertificateManager();
        $manager->validate($certificate);
    }

    public function testCertificateWithExpiryDateBeforeIssueDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date d expiration doit etre posterieure a la date d emission');

        $certificate = $this->makeCertificate();
        $certificate->setIssueDate(new \DateTimeImmutable('2026-05-10'));
        $certificate->setExpiryDate(new \DateTimeImmutable('2026-05-01'));

        $manager = new CertificateManager();
        $manager->validate($certificate);
    }

    public function testCertificateWithoutType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type du certificat est obligatoire');

        $certificate = $this->makeCertificate();
        $certificate->setType('');

        $manager = new CertificateManager();
        $manager->validate($certificate);
    }

    private function makeCertificate(): Certificate
    {
        return (new Certificate())
            ->setCertificateNumber('CERT-2026-001')
            ->setType('ISO')
            ->setStatus('active')
            ->setIssueDate(new \DateTimeImmutable('2026-05-01'))
            ->setExpiryDate(new \DateTimeImmutable('2027-05-01'));
    }
}

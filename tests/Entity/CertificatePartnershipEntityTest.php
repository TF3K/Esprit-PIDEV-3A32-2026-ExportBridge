<?php

namespace App\Tests\Entity;

use App\Entity\Certificate;
use App\Entity\Collaboration;
use App\Entity\Company;
use App\Entity\Partnership;
use App\Entity\Signature;
use PHPUnit\Framework\TestCase;

class CertificatePartnershipEntityTest extends TestCase
{
    public function testCertificateAccessors(): void
    {
        $company = new Company();
        $partnership = new Partnership();
        $issueDate = new \DateTimeImmutable('2026-01-01');
        $expiryDate = new \DateTimeImmutable('2027-01-01');
        $createdAt = new \DateTimeImmutable('2026-05-02 09:00:00');
        $lastUpdated = new \DateTimeImmutable('2026-05-02 10:00:00');

        $certificate = (new Certificate())
            ->setId(14)
            ->setCompany($company)
            ->setType('Certificate of Origin')
            ->setCertificateNumber('CERT-2026-001')
            ->setIssueDate($issueDate)
            ->setExpiryDate($expiryDate)
            ->setStatus('active')
            ->setCountryOfOrigin('TN')
            ->setIssuingAuthority('CEPEX')
            ->setDocumentFile('certificate.pdf')
            ->setCreatedAt($createdAt)
            ->setLastUpdated($lastUpdated);

        $this->assertSame(14, $certificate->getId());
        $this->assertSame($company, $certificate->getCompany());
        $this->assertSame('Certificate of Origin', $certificate->getType());
        $this->assertSame('CERT-2026-001', $certificate->getCertificateNumber());
        $this->assertSame($issueDate, $certificate->getIssueDate());
        $this->assertSame($expiryDate, $certificate->getExpiryDate());
        $this->assertSame('active', $certificate->getStatus());
        $this->assertSame('TN', $certificate->getCountryOfOrigin());
        $this->assertSame('CEPEX', $certificate->getIssuingAuthority());
        $this->assertSame('certificate.pdf', $certificate->getDocumentFile());
        $this->assertSame($createdAt, $certificate->getCreatedAt());
        $this->assertSame($lastUpdated, $certificate->getLastUpdated());
    }

    public function testCertificateSignaturesCollection(): void
    {
        $certificate = new Certificate();
        $signature = new Signature();

        $certificate->addSignature($signature);
        $certificate->addSignature($signature);

        $this->assertCount(1, $certificate->getSignatures());
        $this->assertTrue($certificate->getSignatures()->contains($signature));

        $certificate->removeSignature($signature);

        $this->assertCount(0, $certificate->getSignatures());
    }

    public function testPartnershipAccessors(): void
    {
        $company = new Company();
        $establishedDate = new \DateTimeImmutable('2026-01-10');
        $terminatedDate = new \DateTimeImmutable('2026-12-31');
        $createdAt = new \DateTimeImmutable('2026-05-02 09:00:00');
        $lastUpdated = new \DateTimeImmutable('2026-05-02 10:00:00');

        $partnership = (new Partnership())
            ->setId(8)
            ->setCompany($company)
            ->setStatus('active')
            ->setType('distribution')
            ->setEstablishedDate($establishedDate)
            ->setTerminatedDate($terminatedDate)
            ->setNotes('Strategic partnership')
            ->setCreatedAt($createdAt)
            ->setLastUpdated($lastUpdated);

        $this->assertSame(8, $partnership->getId());
        $this->assertSame($company, $partnership->getCompany());
        $this->assertSame('active', $partnership->getStatus());
        $this->assertSame('distribution', $partnership->getType());
        $this->assertSame($establishedDate, $partnership->getEstablishedDate());
        $this->assertSame($terminatedDate, $partnership->getTerminatedDate());
        $this->assertSame('Strategic partnership', $partnership->getNotes());
        $this->assertSame($createdAt, $partnership->getCreatedAt());
        $this->assertSame($lastUpdated, $partnership->getLastUpdated());
    }

    public function testPartnershipCollaborationsCollection(): void
    {
        $partnership = new Partnership();
        $collaboration = new Collaboration();

        $partnership->addCollaboration($collaboration);
        $partnership->addCollaboration($collaboration);

        $this->assertCount(1, $partnership->getCollaborations());
        $this->assertTrue($partnership->getCollaborations()->contains($collaboration));

        $partnership->removeCollaboration($collaboration);

        $this->assertCount(0, $partnership->getCollaborations());
    }
}

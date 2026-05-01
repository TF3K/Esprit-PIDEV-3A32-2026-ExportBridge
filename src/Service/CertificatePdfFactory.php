<?php

namespace App\Service;

use App\Entity\Certificate;

class CertificatePdfFactory
{
    public function __construct(private readonly SimplePdfGenerator $pdfGenerator)
    {
    }

    public function generate(Certificate $certificate): string
    {
        $company = $certificate->getCompany();
        $partnership = $certificate->getPartnership();

        return $this->pdfGenerator->generateCertificateDesign([
            'title' => 'Certificat de participation',
            'company' => $company?->getCompanyName() ?: 'ExportBridge',
            'recipient' => $company?->getCompanyName() ?: 'Company',
            'issuer' => $certificate->getIssuingAuthority() ?: 'ExportBridge',
            'certificate_number' => $certificate->getCertificateNumber() ?: 'N/A',
            'type' => $certificate->getType() ?: 'Certificate',
            'status' => ucfirst((string) ($certificate->getStatus() ?: 'pending')),
            'country' => $certificate->getCountryOfOrigin() ?: ($company?->getCountry() ?: 'N/A'),
            'issue_date' => $certificate->getIssueDate()?->format('d/m/Y') ?: 'N/A',
            'expiry_date' => $certificate->getExpiryDate()?->format('d/m/Y') ?: 'N/A',
            'partnership' => $partnership ? ($partnership->getType() ?: ('#' . $partnership->getId())) : 'N/A',
        ]);
    }

    public function fileName(Certificate $certificate): string
    {
        return 'certificate-' . $this->safeFileName((string) ($certificate->getCertificateNumber() ?: $certificate->getId())) . '.pdf';
    }

    private function safeFileName(string $value): string
    {
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?? 'file';
        $value = trim($value, '-_.');

        return $value !== '' ? strtolower($value) : 'file';
    }
}

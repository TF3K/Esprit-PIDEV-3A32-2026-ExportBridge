<?php

namespace App\Service;

use App\Entity\Certificate;

class CertificateImageFactory
{
    public function generate(Certificate $certificate): string
    {
        $company = $certificate->getCompany();
        $partnership = $certificate->getPartnership();

        $companyName = $this->text($company?->getCompanyName() ?: 'ExportBridge');
        $recipient = $this->text($company?->getCompanyName() ?: 'Company');
        $issuer = $this->text($certificate->getIssuingAuthority() ?: 'ExportBridge');
        $certificateNumber = $this->text($certificate->getCertificateNumber() ?: 'N/A');
        $type = $this->text($certificate->getType() ?: 'Certificate');
        $status = strtoupper($this->text($certificate->getStatus() ?: 'pending'));
        $country = $this->text($certificate->getCountryOfOrigin() ?: ($company?->getCountry() ?: 'N/A'));
        $issueDate = $certificate->getIssueDate()?->format('d/m/Y') ?: 'N/A';
        $expiryDate = $certificate->getExpiryDate()?->format('d/m/Y') ?: 'N/A';
        $partnershipLabel = $partnership ? $this->text($partnership->getType() ?: ('#' . $partnership->getId())) : 'N/A';

        $description = sprintf(
            '%s certifies that %s has a registered %s certificate. Certificate number %s was issued by %s on %s.',
            $companyName,
            $recipient,
            $type,
            $certificateNumber,
            $issuer,
            $issueDate
        );

        if ($country !== 'N/A') {
            $description .= ' Country of origin: ' . $country . '.';
        }
        if ($partnershipLabel !== 'N/A') {
            $description .= ' Linked partnership: ' . $partnershipLabel . '.';
        }

        $descriptionLines = array_slice($this->wrap($description, 92), 0, 4);
        $descriptionSvg = '';
        $y = 342;
        foreach ($descriptionLines as $line) {
            $descriptionSvg .= sprintf('<text x="500" y="%d" class="body" text-anchor="middle">%s</text>', $y, $this->escape($line));
            $y += 24;
        }

        return sprintf(
            <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="700" viewBox="0 0 1000 700">
  <defs>
    <style>
      .brand { font: 700 19px Arial, sans-serif; fill: #182047; letter-spacing: .5px; }
      .title { font: 500 48px Georgia, serif; fill: #10183d; }
      .small-title { font: 700 15px Arial, sans-serif; fill: #9a7935; letter-spacing: 2px; }
      .recipient { font: italic 600 56px Georgia, serif; fill: #10183d; }
      .body { font: 18px Arial, sans-serif; fill: #26304f; }
      .label { font: 14px Arial, sans-serif; fill: #3f4764; }
      .strong { font: 700 16px Arial, sans-serif; fill: #182047; }
      .seal { font: 700 30px Arial, sans-serif; fill: #9a7935; }
      .seal-small { font: 700 11px Arial, sans-serif; fill: #9a7935; letter-spacing: 1px; }
    </style>
  </defs>
  <rect width="1000" height="700" fill="#dde8f3"/>
  <rect x="90" y="72" width="820" height="556" rx="0" fill="#1e2852"/>
  <rect x="116" y="98" width="768" height="504" fill="#fff" stroke="#d5ae54" stroke-width="3"/>
  <rect x="134" y="116" width="732" height="468" fill="none" stroke="#d5ae54" stroke-width="1.4"/>
  <circle cx="112" cy="82" r="6" fill="#d5ae54"/>
  <circle cx="888" cy="82" r="6" fill="#d5ae54"/>
  <circle cx="112" cy="618" r="6" fill="#d5ae54"/>
  <circle cx="888" cy="618" r="6" fill="#d5ae54"/>

  <text x="500" y="165" class="brand" text-anchor="middle">%s</text>
  <text x="500" y="235" class="title" text-anchor="middle">Certificat de participation</text>
  <text x="500" y="282" class="small-title" text-anchor="middle">THIS CERTIFICATE IS AWARDED TO</text>
  <text x="500" y="332" class="recipient" text-anchor="middle">%s</text>
  <line x1="280" y1="352" x2="720" y2="352" stroke="#d5ae54" stroke-width="2"/>
  %s

  <line x1="205" y1="520" x2="350" y2="520" stroke="#d5ae54" stroke-width="1.5"/>
  <text x="277" y="503" class="strong" text-anchor="middle">%s</text>
  <text x="277" y="544" class="label" text-anchor="middle">Issuer</text>

  <line x1="650" y1="520" x2="795" y2="520" stroke="#d5ae54" stroke-width="1.5"/>
  <text x="722" y="503" class="strong" text-anchor="middle">ExportBridge</text>
  <text x="722" y="544" class="label" text-anchor="middle">Platform</text>

  <rect x="452" y="485" width="96" height="76" fill="none" stroke="#d5ae54" stroke-width="2"/>
  <text x="500" y="525" class="seal" text-anchor="middle">%s</text>
  <text x="500" y="546" class="seal-small" text-anchor="middle">%s</text>

  <text x="250" y="612" class="label" text-anchor="middle">Identifier: %s</text>
  <text x="750" y="612" class="label" text-anchor="middle">Issue date: %s</text>
  <text x="500" y="642" class="label" text-anchor="middle">Expiry: %s | Country: %s</text>
</svg>
SVG,
            $this->escape($companyName),
            $this->escape($recipient),
            $descriptionSvg,
            $this->escape($issuer),
            $this->escape($this->yearFromDate($issueDate)),
            $this->escape($status),
            $this->escape($certificateNumber),
            $this->escape($issueDate),
            $this->escape($expiryDate),
            $this->escape($country)
        );
    }

    public function fileName(Certificate $certificate): string
    {
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) ($certificate->getCertificateNumber() ?: $certificate->getId())) ?: 'certificate';
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?? 'certificate';
        $value = trim($value, '-_.');

        return 'certificate-' . ($value !== '' ? strtolower($value) : 'certificate') . '.svg';
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, int $width): array
    {
        return array_filter(explode("\n", wordwrap($text, $width, "\n", true)));
    }

    private function text(string $value): string
    {
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', ' ', $value) ?? '';

        return trim($value) ?: 'N/A';
    }

    private function yearFromDate(string $date): string
    {
        return preg_match('/(\d{4})/', $date, $match) ? $match[1] : date('Y');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

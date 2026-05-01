<?php

namespace App\Service;

class SimplePdfGenerator
{
    private const PAGE_WIDTH = 595;
    private const PAGE_HEIGHT = 842;
    private const LEFT = 50;
    private const RIGHT = 545;
    private const TOP = 790;
    private const BOTTOM = 60;

    public function generateFromLines(array $lines): string
    {
        $rows = [];
        foreach ($lines as $index => $line) {
            $rows[] = ['label' => 'Line ' . ($index + 1), 'value' => (string) $line];
        }

        return $this->generateDocument('ExportBridge PDF', 'Generated details', [
            ['title' => 'Details', 'rows' => $rows],
        ]);
    }

    /**
     * @param array<int, array{title?: string, rows?: array<int, array{label: string, value: mixed}>, paragraphs?: array<int, mixed>}> $sections
     * @param array<int, mixed> $footerLines
     */
    public function generateDocument(string $title, ?string $subtitle, array $sections, array $footerLines = []): string
    {
        $pages = [];
        $content = '';
        $y = self::TOP;
        $pageNo = 1;

        $newPage = function () use (&$pages, &$content, &$y, &$pageNo): void {
            if ($content !== '') {
                $this->addText($content, self::LEFT, 36, 'Page ' . $pageNo, 8);
                $pages[] = $content;
                $pageNo++;
            }
            $content = '';
            $y = self::TOP;
        };

        $ensureSpace = function (int $needed) use (&$y, $newPage): void {
            if ($y - $needed < self::BOTTOM) {
                $newPage();
            }
        };

        $this->addText($content, self::LEFT, $y, $title, 22, true);
        $y -= 24;
        if ($subtitle) {
            $this->addText($content, self::LEFT, $y, $subtitle, 10);
            $y -= 18;
        }
        $this->addLine($content, self::LEFT, $y, self::RIGHT, $y);
        $y -= 28;

        foreach ($sections as $section) {
            $ensureSpace(48);
            $sectionTitle = trim((string) ($section['title'] ?? ''));
            if ($sectionTitle !== '') {
                $this->addText($content, self::LEFT, $y, $sectionTitle, 15, true);
                $y -= 20;
            }

            foreach (($section['rows'] ?? []) as $row) {
                $label = (string) ($row['label'] ?? '');
                $value = $this->stringValue($row['value'] ?? 'N/A');
                $valueLines = $this->wrapText($value, 64);
                $height = max(1, count($valueLines)) * 13 + 6;
                $ensureSpace($height + 4);

                $this->addText($content, self::LEFT + 10, $y, $label . ':', 10, true);
                $lineY = $y;
                foreach ($valueLines as $line) {
                    $this->addText($content, self::LEFT + 160, $lineY, $line, 10);
                    $lineY -= 13;
                }
                $y -= $height;
            }

            foreach (($section['paragraphs'] ?? []) as $paragraph) {
                $lines = $this->wrapText($this->stringValue($paragraph), 86);
                $height = max(1, count($lines)) * 13 + 8;
                $ensureSpace($height + 4);
                $lineY = $y;
                foreach ($lines as $line) {
                    $this->addText($content, self::LEFT + 10, $lineY, $line, 10);
                    $lineY -= 13;
                }
                $y -= $height;
            }

            $y -= 8;
        }

        if ($footerLines) {
            $ensureSpace(40);
            $this->addLine($content, self::LEFT, $y, self::RIGHT, $y);
            $y -= 18;
            foreach ($footerLines as $footerLine) {
                $this->addText($content, self::LEFT, $y, $this->stringValue($footerLine), 8);
                $y -= 11;
            }
        }

        $newPage();

        return $this->buildPdf($pages);
    }

    private function buildPdf(array $pages): string
    {
        $objects = [];
        $pageObjectNumbers = [];
        $fontRegularObject = 3 + (count($pages) * 2);
        $fontBoldObject = $fontRegularObject + 1;

        $objects[1] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $contentObject = 4;
        foreach ($pages as $index => $pageContent) {
            $pageObject = 3 + ($index * 2);
            $contentObject = $pageObject + 1;
            $pageObjectNumbers[] = $pageObject . ' 0 R';
            $objects[$pageObject] = sprintf(
                '%d 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >> endobj',
                $pageObject,
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $fontRegularObject,
                $fontBoldObject,
                $contentObject
            );
            $objects[$contentObject] = sprintf(
                "%d 0 obj << /Length %d >> stream\n%s\nendstream endobj",
                $contentObject,
                strlen($pageContent),
                $pageContent
            );
        }

        $objects[2] = '2 0 obj << /Type /Pages /Kids [' . implode(' ', $pageObjectNumbers) . '] /Count ' . count($pages) . ' >> endobj';
        $objects[$fontRegularObject] = $fontRegularObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[$fontBoldObject] = $fontBoldObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 " . $size . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i] ?? 0) . "\n";
        }
        $pdf .= "trailer << /Size " . $size . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private function addText(string &$content, int $x, int $y, string $text, int $fontSize = 10, bool $bold = false): void
    {
        $font = $bold ? 'F2' : 'F1';
        $content .= sprintf("BT /%s %d Tf %d %d Td (%s) Tj ET\n", $font, $fontSize, $x, $y, $this->escape($this->normalise($text)));
    }

    private function addLine(string &$content, int $x1, int $y1, int $x2, int $y2): void
    {
        $content .= sprintf("0.8 w %d %d m %d %d l S\n", $x1, $y1, $x2, $y2);
    }

    private function wrapText(string $text, int $width): array
    {
        $text = trim($this->normalise($text));
        if ($text === '') {
            return ['N/A'];
        }

        $lines = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $paragraph) {
            $wrapped = wordwrap(trim($paragraph), $width, "\n", true);
            foreach (explode("\n", $wrapped) as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        return $lines ?: ['N/A'];
    }

    private function stringValue(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value === null || $value === '') {
            return 'N/A';
        }

        return (string) $value;
    }

    private function normalise(string $text): string
    {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', ' ', $text) ?? '';
        $text = str_replace(['–', '—', '•'], ['-', '-', '-'], $text);

        return $text;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    /**
     * Generate a designed certificate PDF from the certificate fields.
     * This is intentionally separate from uploaded PDFs: uploaded PDFs are opened inline,
     * while this PDF is generated/downloaded from database fields.
     *
     * @param array<string, mixed> $data
     */
    public function generateCertificateDesign(array $data): string
    {
        $pageWidth = 842;
        $pageHeight = 595;
        $content = '';

        $company = $this->normalise((string) ($data['company'] ?? 'ExportBridge'));
        $recipient = $this->normalise((string) ($data['recipient'] ?? $company));
        $title = $this->normalise((string) ($data['title'] ?? 'Certificat de participation'));
        $issuer = $this->normalise((string) ($data['issuer'] ?? 'ExportBridge'));
        $certificateNumber = $this->normalise((string) ($data['certificate_number'] ?? 'N/A'));
        $certificateType = $this->normalise((string) ($data['type'] ?? 'Certificate'));
        $status = strtoupper($this->normalise((string) ($data['status'] ?? 'Pending')));
        $country = $this->normalise((string) ($data['country'] ?? 'N/A'));
        $issueDate = $this->normalise((string) ($data['issue_date'] ?? 'N/A'));
        $expiryDate = $this->normalise((string) ($data['expiry_date'] ?? 'N/A'));
        $partnership = $this->normalise((string) ($data['partnership'] ?? 'N/A'));

        $description = sprintf(
            '%s certifies that %s has a registered %s certificate. Certificate number %s was issued by %s on %s and is linked to ExportBridge records.',
            $company,
            $recipient,
            $certificateType,
            $certificateNumber,
            $issuer,
            $issueDate
        );

        if ($expiryDate !== 'N/A') {
            $description .= ' Expiry date: ' . $expiryDate . '.';
        }
        if ($country !== 'N/A') {
            $description .= ' Country of origin: ' . $country . '.';
        }
        if ($partnership !== 'N/A') {
            $description .= ' Linked partnership: ' . $partnership . '.';
        }

        // Background and outer frame, inspired by the provided certificate design.
        $this->pdfRect($content, 0, 0, $pageWidth, $pageHeight, [0.86, 0.91, 0.96], [0.86, 0.91, 0.96], 1);
        $this->pdfRect($content, 88, 70, 666, 455, [0.12, 0.16, 0.39], [0.12, 0.16, 0.39], 2);
        $this->pdfRect($content, 108, 90, 626, 415, [0.84, 0.69, 0.37], [1, 1, 1], 2);
        $this->pdfRect($content, 122, 104, 598, 387, [0.84, 0.69, 0.37], null, 1);

        // Decorative gold dots.
        foreach ([[102, 502], [740, 502], [102, 92], [740, 92]] as $dot) {
            $this->pdfCircle($content, $dot[0], $dot[1], 5, [0.90, 0.70, 0.35]);
        }

        $this->pdfText($content, 421, 455, $company, 13, 'F2', 'center', [0.10, 0.13, 0.36]);
        $this->pdfText($content, 421, 395, $title, 34, 'F4', 'center', [0.06, 0.09, 0.30]);
        $this->pdfText($content, 421, 360, 'THIS CERTIFICATE IS AWARDED TO', 12, 'F2', 'center', [0.62, 0.50, 0.27]);
        $this->pdfText($content, 421, 300, $recipient, 44, 'F3', 'center', [0.06, 0.09, 0.30]);
        $this->pdfLine($content, 240, 278, 602, 278, 1.3, [0.84, 0.69, 0.37]);

        $lines = $this->wrapText($description, 82);
        $y = 244;
        foreach (array_slice($lines, 0, 5) as $line) {
            $this->pdfText($content, 421, $y, $line, 13, 'F1', 'center', [0.15, 0.18, 0.30]);
            $y -= 17;
        }

        $this->pdfLine($content, 190, 150, 315, 150, 1, [0.84, 0.69, 0.37]);
        $this->pdfText($content, 252, 158, $issuer, 11, 'F2', 'center', [0.08, 0.11, 0.33]);
        $this->pdfText($content, 252, 134, 'Issuer', 10, 'F1', 'center', [0.20, 0.22, 0.35]);

        $this->pdfLine($content, 527, 150, 652, 150, 1, [0.84, 0.69, 0.37]);
        $this->pdfText($content, 590, 158, 'ExportBridge', 11, 'F2', 'center', [0.08, 0.11, 0.33]);
        $this->pdfText($content, 590, 134, 'Platform', 10, 'F1', 'center', [0.20, 0.22, 0.35]);

        $this->pdfRect($content, 382, 108, 78, 62, [0.84, 0.69, 0.37], null, 1);
        $year = preg_match('/(\d{4})/', $issueDate, $m) ? $m[1] : date('Y');
        $this->pdfText($content, 421, 141, $year, 23, 'F2', 'center', [0.62, 0.50, 0.27]);
        $this->pdfText($content, 421, 121, $status, 8, 'F2', 'center', [0.62, 0.50, 0.27]);

        $this->pdfText($content, 220, 96, 'Identifier: ' . $certificateNumber, 10, 'F1', 'center', [0.18, 0.20, 0.33]);
        $this->pdfText($content, 606, 96, 'Issue date: ' . $issueDate, 10, 'F1', 'center', [0.18, 0.20, 0.33]);
        $this->pdfText($content, 421, 90, 'Expiry: ' . $expiryDate . ' | Country: ' . $country, 9, 'F1', 'center', [0.18, 0.20, 0.33]);

        return $this->buildCustomPdf([$content], $pageWidth, $pageHeight);
    }

    /** @param array<int, string> $pages */
    private function buildCustomPdf(array $pages, int $pageWidth, int $pageHeight): string
    {
        $objects = [];
        $pageObjectNumbers = [];
        $fontRegularObject = 3 + (count($pages) * 2);
        $fontBoldObject = $fontRegularObject + 1;
        $fontItalicObject = $fontRegularObject + 2;
        $fontTitleObject = $fontRegularObject + 3;

        $objects[1] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        foreach ($pages as $index => $pageContent) {
            $pageObject = 3 + ($index * 2);
            $contentObject = $pageObject + 1;
            $pageObjectNumbers[] = $pageObject . ' 0 R';
            $objects[$pageObject] = sprintf(
                '%d 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R /F3 %d 0 R /F4 %d 0 R >> >> /Contents %d 0 R >> endobj',
                $pageObject,
                $pageWidth,
                $pageHeight,
                $fontRegularObject,
                $fontBoldObject,
                $fontItalicObject,
                $fontTitleObject,
                $contentObject
            );
            $objects[$contentObject] = sprintf(
                "%d 0 obj << /Length %d >> stream\n%s\nendstream endobj",
                $contentObject,
                strlen($pageContent),
                $pageContent
            );
        }

        $objects[2] = '2 0 obj << /Type /Pages /Kids [' . implode(' ', $pageObjectNumbers) . '] /Count ' . count($pages) . ' >> endobj';
        $objects[$fontRegularObject] = $fontRegularObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[$fontBoldObject] = $fontBoldObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj';
        $objects[$fontItalicObject] = $fontItalicObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Times-Italic >> endobj';
        $objects[$fontTitleObject] = $fontTitleObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Times-Roman >> endobj';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 " . $size . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i] ?? 0) . "\n";
        }
        $pdf .= "trailer << /Size " . $size . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private function pdfText(string &$content, float $x, float $y, string $text, int $fontSize = 10, string $font = 'F1', string $align = 'left', array $color = [0, 0, 0]): void
    {
        $escaped = $this->escape($this->normalise($text));
        if ($align === 'center') {
            $x -= $this->estimatePdfTextWidth($escaped, $fontSize, $font) / 2;
        } elseif ($align === 'right') {
            $x -= $this->estimatePdfTextWidth($escaped, $fontSize, $font);
        }
        $content .= sprintf("q %.3F %.3F %.3F rg BT /%s %d Tf %.2F %.2F Td (%s) Tj ET Q\n", $color[0], $color[1], $color[2], $font, $fontSize, $x, $y, $escaped);
    }

    private function pdfLine(string &$content, float $x1, float $y1, float $x2, float $y2, float $width = 1, array $color = [0, 0, 0]): void
    {
        $content .= sprintf("q %.3F %.3F %.3F RG %.2F w %.2F %.2F m %.2F %.2F l S Q\n", $color[0], $color[1], $color[2], $width, $x1, $y1, $x2, $y2);
    }

    private function pdfRect(string &$content, float $x, float $y, float $w, float $h, array $strokeColor = [0, 0, 0], ?array $fillColor = null, float $lineWidth = 1): void
    {
        if ($fillColor !== null) {
            $content .= sprintf("q %.3F %.3F %.3F RG %.3F %.3F %.3F rg %.2F w %.2F %.2F %.2F %.2F re B Q\n", $strokeColor[0], $strokeColor[1], $strokeColor[2], $fillColor[0], $fillColor[1], $fillColor[2], $lineWidth, $x, $y, $w, $h);
            return;
        }
        $content .= sprintf("q %.3F %.3F %.3F RG %.2F w %.2F %.2F %.2F %.2F re S Q\n", $strokeColor[0], $strokeColor[1], $strokeColor[2], $lineWidth, $x, $y, $w, $h);
    }

    private function pdfCircle(string &$content, float $x, float $y, float $r, array $color): void
    {
        $content .= sprintf("q %.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f Q\n", $color[0], $color[1], $color[2], $x - $r, $y - $r, $r * 2, $r * 2);
    }

    private function estimatePdfTextWidth(string $text, int $fontSize, string $font): float
    {
        $factor = in_array($font, ['F3', 'F4'], true) ? 0.42 : 0.52;
        return strlen($this->normalise($text)) * $fontSize * $factor;
    }

}

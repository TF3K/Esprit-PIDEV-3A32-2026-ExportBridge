<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiCertificateExtractorService
{
    private const OPENAI_RESPONSES_URL = 'https://api.openai.com/v1/responses';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $openAiApiKey = '',
        private readonly string $demoMode = '0'
    ) {
    }

    public function extractFromPdf(UploadedFile $file): array
    {
        if ($this->isDemoMode()) {
            return $this->demoResult($file);
        }

        if (trim($this->openAiApiKey) === '') {
            return $this->errorResult('OpenAI API key is missing. Add OPENAI_API_KEY in your .env.local file, or set AI_CERTIFICATE_DEMO_MODE=1 for presentation mode.');
        }

        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            return $this->errorResult('The uploaded PDF could not be read.');
        }

        if ($file->getSize() !== null && $file->getSize() > 50 * 1024 * 1024) {
            return $this->errorResult('The PDF is too large for AI extraction. Please use a PDF smaller than 50 MB.');
        }

        $base64Pdf = base64_encode((string) file_get_contents($path));
        $originalName = $file->getClientOriginalName() ?: 'certificate.pdf';

        try {
            $response = $this->httpClient->request('POST', self::OPENAI_RESPONSES_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 90,
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'input' => [[
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_file',
                                'filename' => $originalName,
                                'file_data' => 'data:application/pdf;base64,' . $base64Pdf,
                            ],
                            [
                                'type' => 'input_text',
                                'text' => $this->buildPrompt(),
                            ],
                        ],
                    ]],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'certificate_auto_fill',
                            'strict' => true,
                            'schema' => $this->schema(),
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($statusCode < 200 || $statusCode >= 300) {
                $message = $data['error']['message'] ?? 'OpenAI request failed.';
                return $this->errorResult($message);
            }

            $jsonText = $data['output_text'] ?? $this->extractOutputText($data);
            $jsonText = $this->cleanJsonText($jsonText);
            $decoded = json_decode($jsonText, true);

            if (!is_array($decoded)) {
                return $this->errorResult('AI returned an unreadable response. Please try again.');
            }

            return [
                'success' => true,
                'fields' => [
                    'certificate_number' => $this->normalizeValue($decoded['certificate_number'] ?? ''),
                    'type' => $this->normalizeValue($decoded['certificate_type'] ?? ''),
                    'issuing_authority' => $this->normalizeValue($decoded['issuing_authority'] ?? ''),
                    'country_of_origin' => $this->normalizeValue($decoded['country_of_origin'] ?? ''),
                    'issue_date' => $this->normalizeDate($decoded['issue_date'] ?? ''),
                    'expiry_date' => $this->normalizeDate($decoded['expiry_date'] ?? ''),
                ],
                'confidence' => max(0, min(100, (int) ($decoded['confidence'] ?? 0))),
                'summary' => $this->normalizeValue($decoded['summary'] ?? ''),
                'warnings' => array_values(array_filter(array_map([$this, 'normalizeValue'], $decoded['warnings'] ?? []))),
            ];
        } catch (\Throwable $exception) {
            return $this->errorResult('AI extraction failed: ' . $exception->getMessage());
        }
    }

    private function isDemoMode(): bool
    {
        return in_array(strtolower(trim($this->demoMode)), ['1', 'true', 'yes', 'on', 'demo'], true);
    }

    private function demoResult(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName() ?: 'certificate.pdf';
        $lowerName = strtolower($originalName);

        $type = 'Certificate of Origin';
        if (str_contains($lowerName, 'quality')) {
            $type = 'Quality Certificate';
        } elseif (str_contains($lowerName, 'health') || str_contains($lowerName, 'sanitary')) {
            $type = 'Health Certificate';
        } elseif (str_contains($lowerName, 'export')) {
            $type = 'Export Certificate';
        }

        $today = new \DateTimeImmutable('today');
        $certificateHash = strtoupper(substr(sha1($originalName . (string) $file->getSize()), 0, 6));

        return [
            'success' => true,
            'demo_mode' => true,
            'fields' => [
                'certificate_number' => 'DEMO-' . $certificateHash,
                'type' => $type,
                'issuing_authority' => 'Chamber of Commerce and Industry',
                'country_of_origin' => 'Tunisia',
                'issue_date' => $today->format('Y-m-d'),
                'expiry_date' => $today->modify('+1 year')->format('Y-m-d'),
            ],
            'confidence' => 88,
            'summary' => 'Demo mode generated realistic certificate data for presentation without paid OpenAI API credits.',
            'warnings' => [
                'Demo mode is enabled because no paid API quota is available.',
                'For real extraction from PDF content, add billing credits and set AI_CERTIFICATE_DEMO_MODE=0.',
                'Review the generated fields before saving the certificate.',
            ],
        ];
    }

    private function buildPrompt(): string
    {
        return <<<PROMPT
You are helping an export platform auto-fill a certificate form from an uploaded certificate PDF.
Extract only data that is visible or strongly implied in the document.
Return JSON only using the provided schema.
Rules:
- Use an empty string for any missing value.
- Dates must be ISO format YYYY-MM-DD when possible. If the date is unclear, return an empty string.
- certificate_type should be a short business label such as Certificate of Origin, Quality Certificate, Health Certificate, Export Certificate, or the closest label visible in the document.
- certificate_number should be the official certificate/reference number, not the company registration number.
- confidence is an integer from 0 to 100.
- warnings should explain uncertain or missing fields in short user-friendly sentences.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'certificate_number' => ['type' => 'string'],
                'certificate_type' => ['type' => 'string'],
                'issuing_authority' => ['type' => 'string'],
                'country_of_origin' => ['type' => 'string'],
                'issue_date' => ['type' => 'string'],
                'expiry_date' => ['type' => 'string'],
                'confidence' => ['type' => 'integer'],
                'summary' => ['type' => 'string'],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => [
                'certificate_number',
                'certificate_type',
                'issuing_authority',
                'country_of_origin',
                'issue_date',
                'expiry_date',
                'confidence',
                'summary',
                'warnings',
            ],
        ];
    }

    private function extractOutputText(array $data): string
    {
        $chunks = [];
        foreach (($data['output'] ?? []) as $outputItem) {
            foreach (($outputItem['content'] ?? []) as $contentItem) {
                if (($contentItem['type'] ?? '') === 'output_text' && isset($contentItem['text'])) {
                    $chunks[] = (string) $contentItem['text'];
                }
            }
        }

        return trim(implode("\n", $chunks));
    }

    private function cleanJsonText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/', '', $text) ?? $text;

        return trim($text);
    }

    private function normalizeValue(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $value = trim((string) $value);
        $lower = strtolower($value);
        if (in_array($lower, ['n/a', 'na', 'none', 'null', 'not found', 'unknown'], true)) {
            return '';
        }

        return $value;
    }

    private function normalizeDate(mixed $value): string
    {
        $value = $this->normalizeValue($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private function errorResult(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'fields' => [],
            'confidence' => 0,
            'summary' => '',
            'warnings' => [$message],
        ];
    }
}

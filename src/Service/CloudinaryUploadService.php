<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CloudinaryUploadService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $cloudName,
        private readonly string $uploadPreset,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->cloudName !== '' && $this->uploadPreset !== '';
    }

    public function upload(UploadedFile $file): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Cloudinary is not configured. Add CLOUDINARY_CLOUD_NAME and CLOUDINARY_UPLOAD_PRESET in .env.',
            ];
        }

        try {
            $response = $this->httpClient->request('POST', sprintf('https://api.cloudinary.com/v1_1/%s/auto/upload', $this->cloudName), [
                'body' => [
                    'upload_preset' => $this->uploadPreset,
                    'file' => fopen($file->getPathname(), 'r'),
                    'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid(),
                ],
                'timeout' => 60,
            ]);

            $data = $response->toArray(false);
            if (isset($data['error']['message'])) {
                return ['ok' => false, 'message' => (string) $data['error']['message']];
            }

            return [
                'ok' => true,
                'url' => $data['secure_url'] ?? null,
                'public_id' => $data['public_id'] ?? null,
                'original_filename' => $file->getClientOriginalName(),
            ];
        } catch (ExceptionInterface $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

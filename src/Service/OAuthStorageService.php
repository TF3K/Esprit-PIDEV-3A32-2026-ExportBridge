<?php

namespace App\Service;

class OAuthStorageService
{
    private string $storageDir;

    public function __construct(string $projectDir)
    {
        $this->storageDir = $projectDir . '/var/oauth';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function save(string $email, array $data): void
    {
        $path = $this->storagePathForEmail($email);
        $payload = json_encode($data, JSON_PRETTY_PRINT);
        if ($payload === false) {
            return;
        }

        file_put_contents($path, $payload, LOCK_EX);
    }

    public function get(string $email): ?array
    {
        $path = $this->storagePathForEmail($email);
        if (!file_exists($path)) {
            $legacyPath = $this->legacyStoragePathForEmail($email);
            if (file_exists($legacyPath)) {
                $path = $legacyPath;
            }
        }

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function delete(string $email): void
    {
        $path = $this->storagePathForEmail($email);
        if (file_exists($path)) {
            unlink($path);
        }

        $legacyPath = $this->legacyStoragePathForEmail($email);
        if ($legacyPath !== $path && file_exists($legacyPath)) {
            unlink($legacyPath);
        }
    }

    private function storagePathForEmail(string $email): string
    {
        $normalizedEmail = strtolower(trim($email));
        return $this->storageDir . '/' . md5($normalizedEmail) . '.json';
    }

    private function legacyStoragePathForEmail(string $email): string
    {
        return $this->storageDir . '/' . md5(trim($email)) . '.json';
    }
}

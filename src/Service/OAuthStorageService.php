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
        $path = $this->storageDir . '/' . md5($email) . '.json';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function get(string $email): ?array
    {
        $path = $this->storageDir . '/' . md5($email) . '.json';
        if (!file_exists($path)) {
            return null;
        }
        return json_decode(file_get_contents($path), true);
    }

    public function delete(string $email): void
    {
        $path = $this->storageDir . '/' . md5($email) . '.json';
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

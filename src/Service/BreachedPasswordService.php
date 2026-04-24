<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BreachedPasswordService
{
    private const API_URL = 'https://api.pwnedpasswords.com/range/';

    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function getBreachCount(string $password): int
    {
        if ($password === '') {
            return 0;
        }

        $hash = strtoupper(sha1($password));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        try {
            $response = $this->httpClient->request('GET', self::API_URL . $prefix, [
                'headers' => [
                    'Add-Padding' => 'true',
                ],
                'timeout' => 8,
            ]);

            if ($response->getStatusCode() !== 200) {
                return 0;
            }

            $lines = preg_split('/\r\n|\r|\n/', $response->getContent(false)) ?: [];

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || !str_contains($line, ':')) {
                    continue;
                }

                [$hashSuffix, $count] = explode(':', $line, 2);
                if (strtoupper(trim($hashSuffix)) === $suffix) {
                    return (int) trim($count);
                }
            }
        } catch (\Throwable) {
            // Fail open on provider/network issues to avoid blocking admin operations.
            return 0;
        }

        return 0;
    }
}
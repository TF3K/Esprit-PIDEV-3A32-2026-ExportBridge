<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleTranslateService
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function translate(?string $text, string $targetLang): string
    {
        $text = trim((string) $text);
        $targetLang = trim((string) $targetLang);
        if ($text === '' || $targetLang === '') {
            return $text;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.mymemory.translated.net/get', [
                'query' => [
                    'q' => $text,
                    'langpair' => 'auto|' . $targetLang,
                ],
                'timeout' => 20,
            ]);

            $data = $response->toArray(false);
            return (string) ($data['responseData']['translatedText'] ?? $text);
        } catch (\Throwable) {
            return $text;
        }
    }
}

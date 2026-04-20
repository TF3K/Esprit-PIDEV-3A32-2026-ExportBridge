<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailValidationService
{
    private const DEFAULT_API_URL = 'https://emailreputation.abstractapi.com/v1/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $apiBaseUrl = self::DEFAULT_API_URL,
    ) {
    }

    public function getRejectionReason(string $email): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', $this->apiBaseUrl, [
                'query' => [
                    'api_key' => $this->apiKey,
                    'email' => $email,
                ],
                'timeout' => 8,
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = $response->toArray(false);

            // Current Abstract Email Reputation payload.
            if (isset($data['email_deliverability']) || isset($data['email_quality'])) {
                $isFormatValid = (bool) ($data['email_deliverability']['is_format_valid'] ?? false);
                $isMxValid = (bool) ($data['email_deliverability']['is_mx_valid'] ?? false);
                $isSmtpValid = (bool) ($data['email_deliverability']['is_smtp_valid'] ?? false);
                $status = strtolower((string) ($data['email_deliverability']['status'] ?? 'unknown'));
                $isDisposable = (bool) ($data['email_quality']['is_disposable'] ?? false);

                if (!$isFormatValid) {
                    return 'Please enter a valid email address.';
                }

                if ($isDisposable) {
                    return 'Disposable email addresses are not allowed.';
                }

                if (!$isMxValid || !$isSmtpValid) {
                    return 'This email address appears undeliverable.';
                }

                if (in_array($status, ['undeliverable', 'invalid', 'risky'], true)) {
                    return 'This email address appears risky or undeliverable.';
                }

                return null;
            }

            // Legacy/alternative validation-style payload support.
            $isValidFormat = (bool) ($data['is_valid_format']['value'] ?? false);
            $isMxFound = (bool) ($data['is_mx_found']['value'] ?? false);
            $isDisposable = (bool) ($data['is_disposable_email']['value'] ?? false);
            $deliverability = strtolower((string) ($data['deliverability'] ?? 'unknown'));

            if (!$isValidFormat) {
                return 'Please enter a valid email address.';
            }

            if ($isDisposable) {
                return 'Disposable email addresses are not allowed.';
            }

            if (!$isMxFound) {
                return 'Email domain cannot receive mail (missing MX records).';
            }

            if (in_array($deliverability, ['undeliverable', 'do_not_mail'], true)) {
                return 'This email address appears undeliverable.';
            }

            return null;
        } catch (\Throwable) {
            // Fail open on provider/network issues to avoid blocking admin operations.
            return null;
        }
    }

    private function isEnabled(): bool
    {
        return trim($this->apiKey) !== '';
    }
}
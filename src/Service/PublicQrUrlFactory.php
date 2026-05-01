<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PublicQrUrlFactory
{
    public function absoluteUrl(Request $request, string $path): string
    {
        $baseUrl = $this->configuredBaseUrl() ?? $this->requestBaseUrl($request);

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function configuredBaseUrl(): ?string
    {
        $configured = rtrim((string) ($_ENV['APP_PUBLIC_URL'] ?? $_SERVER['APP_PUBLIC_URL'] ?? ''), '/');
        if ($configured === '') {
            return null;
        }

        $host = parse_url($configured, PHP_URL_HOST);
        if (!is_string($host) || $host === '' || $this->isLocalHost($host) || $this->isIpAddress($host)) {
            return null;
        }

        return $configured;
    }

    private function requestBaseUrl(Request $request): string
    {
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();

        if ($this->isLocalHost($host)) {
            $host = $this->detectLanIp() ?? $host;
            $scheme = 'http';
        }

        return sprintf('%s://%s', $scheme, $this->hostWithPort($host, $port, $scheme));
    }

    protected function detectLanIp(): ?string
    {
        $socket = @stream_socket_client('udp://8.8.8.8:80', $errno, $error, 0.2);
        if (is_resource($socket)) {
            $localName = stream_socket_get_name($socket, false);
            fclose($socket);

            if (is_string($localName) && preg_match('/^(\d{1,3}(?:\.\d{1,3}){3}):\d+$/', $localName, $matches) === 1) {
                if ($this->isUsableLanIp($matches[1])) {
                    return $matches[1];
                }
            }
        }

        foreach (gethostbynamel(gethostname()) ?: [] as $candidate) {
            if ($this->isUsableLanIp($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function hostWithPort(string $host, int $port, string $scheme): string
    {
        $formattedHost = str_contains($host, ':') && $host[0] !== '[' ? '[' . $host . ']' : $host;
        $defaultPort = $scheme === 'https' ? 443 : 80;

        return $port === $defaultPort ? $formattedHost : sprintf('%s:%d', $formattedHost, $port);
    }

    private function isLocalHost(string $host): bool
    {
        $host = trim(strtolower($host), '[]');

        return in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true);
    }

    private function isIpAddress(string $host): bool
    {
        return filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false;
    }

    private function isUsableLanIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        return !str_starts_with($ip, '127.')
            && !str_starts_with($ip, '169.254.')
            && $ip !== '0.0.0.0';
    }
}

<?php

namespace Modules\Glitter\Analytics\Services;

use InvalidArgumentException;

class AnalyticsRequestNormalizer
{
    public function path(string $value): string
    {
        $value = trim($value);
        if ($value === '' || ! str_starts_with($value, '/') || str_starts_with($value, '//') || preg_match('/[\x00-\x1F\x7F]/u', $value)) {
            throw new InvalidArgumentException('Invalid page path.');
        }

        $normalized = preg_split('/[?#]/', $value, 2)[0] ?? '/';
        $normalized = $normalized === '' ? '/' : $normalized;
        if (mb_strlen($normalized) > 512) {
            throw new InvalidArgumentException('Normalized page path is too long.');
        }

        return $normalized;
    }

    /** @return array{host: string, path: string}|null */
    public function referrer(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $parts = parse_url(trim($value));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! is_array($parts) || ! in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new InvalidArgumentException('Invalid referrer URL.');
        }

        $path = (string) ($parts['path'] ?? '/');
        $path = $path === '' ? '/' : $path;
        if (mb_strlen($path) > 512 || mb_strlen($host) > 255) {
            throw new InvalidArgumentException('Normalized referrer is too long.');
        }

        $port = $parts['port'] ?? null;
        if ($port !== null && ! (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
            $host .= ':'.$port;
        }

        return ['host' => $host, 'path' => $path];
    }
}

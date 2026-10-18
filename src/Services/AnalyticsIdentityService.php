<?php

namespace Modules\Glitter\Analytics\Services;

class AnalyticsIdentityService
{
    private string $key;

    public function __construct(?string $secret = null)
    {
        $secret ??= (string) config('app.key');
        $this->key = hash_hmac('sha256', 'glitter-analytics:key', $secret, true);
    }

    public function ipHash(?string $ip, string $mode): ?string
    {
        if ($mode === 'none' || $ip === null) {
            return null;
        }

        $packed = @inet_pton($ip);
        if ($packed === false) {
            return null;
        }

        $normalized = (string) inet_ntop($packed);
        if ($mode === 'mask') {
            $normalized = strlen($packed) === 4
                ? inet_ntop(substr($packed, 0, 3)."\0")
                : inet_ntop(substr($packed, 0, 8).str_repeat("\0", 8));
        }

        return hash_hmac('sha256', 'analytics:ip:'.$mode.':'.$normalized, $this->key);
    }

    public function visitorHash(?string $clientId): ?string
    {
        return $this->clientHash($clientId, 'analytics:visitor');
    }

    public function sessionHash(?string $clientId): ?string
    {
        return $this->clientHash($clientId, 'analytics:session');
    }

    private function clientHash(?string $clientId, string $purpose): ?string
    {
        return $clientId === null ? null : hash_hmac('sha256', $purpose.':'.$clientId, $this->key);
    }
}

<?php

namespace Modules\Glitter\Analytics\Services;

use App\Contracts\Extension\CacheInterface;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;

class AnalyticsDuplicateGuard
{
    public function __construct(private CacheInterface $cache) {}

    public function key(?string $identityHash, string $path, AnalyticsEventSource $source): string
    {
        return 'pageview:duplicate:'.hash('sha256', implode('|', [$identityHash ?? '-', $source->value, $path]));
    }

    public function acquire(string $key): bool
    {
        try {
            if ($this->cache->get($key) !== null) {
                return false;
            }

            return $this->cache->put($key, true, 5);
        } catch (\Throwable) {
            return true;
        }
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }
}

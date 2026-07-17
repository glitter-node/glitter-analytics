<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use App\Contracts\Extension\CacheInterface;
use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\DTO\AnalyticsRequestContext;
use Modules\Glitter\Analytics\DTO\PageViewInputData;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Enums\AnalyticsTrackingDecision;
use Modules\Glitter\Analytics\Services\AnalyticsDuplicateGuard;
use Modules\Glitter\Analytics\Services\AnalyticsIdentityService;
use Modules\Glitter\Analytics\Services\AnalyticsRequestNormalizer;
use PHPUnit\Framework\TestCase;

class AnalyticsPageViewCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['Enums/AnalyticsEventSource.php', 'Enums/AnalyticsTrackingDecision.php', 'DTO/PageViewInputData.php', 'DTO/AnalyticsRequestContext.php', 'Services/AnalyticsRequestNormalizer.php', 'Services/AnalyticsIdentityService.php', 'Services/AnalyticsDuplicateGuard.php'] as $file) {
            require_once dirname(__DIR__, 2).'/src/'.$file;
        }
    }

    public function test_normalizes_path_and_referrer_without_sensitive_parts(): void
    {
        $normalizer = new AnalyticsRequestNormalizer;

        $this->assertSame('/boards/자유', $normalizer->path('/boards/자유?page=2#comments'));
        $this->assertSame('/', $normalizer->path('/'));
        $this->assertSame(
            ['host' => 'example.com', 'path' => '/Path'],
            $normalizer->referrer('https://user:pass@Example.COM:443/Path?a=1#fragment'),
        );
    }

    public function test_identity_hashes_ip_without_returning_fingerprint_identifiers(): void
    {
        $service = new AnalyticsIdentityService('test-secret');

        $this->assertSame($service->ipHash('192.0.2.25', 'hash'), $service->ipHash('192.0.2.25', 'hash'));
        $this->assertSame($service->ipHash('192.0.2.25', 'mask'), $service->ipHash('192.0.2.200', 'mask'));
        $this->assertSame($service->ipHash('2001:db8::1', 'mask'), $service->ipHash('2001:db8::ffff', 'mask'));
        $this->assertNotSame($service->ipHash('192.0.2.25', 'hash'), $service->ipHash('192.0.2.25', 'mask'));
        $this->assertNull($service->ipHash('192.0.2.25', 'none'));
        $this->assertNull($service->visitorHash(null));
        $this->assertNull($service->sessionHash(null));
        $visitor = $service->visitorHash('018f0d34-1abc-4def-8abc-0123456789ab');
        $session = $service->sessionHash('018f0d34-1abc-4def-8abc-0123456789ab');
        $this->assertSame(64, strlen((string) $visitor));
        $this->assertSame($visitor, $service->visitorHash('018f0d34-1abc-4def-8abc-0123456789ab'));
        $this->assertNotSame($visitor, $session);
    }

    public function test_duplicate_guard_uses_only_hashed_cache_key(): void
    {
        $values = [];
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(function (string $key) use (&$values) {
            return $values[$key] ?? null;
        });
        $cache->method('put')->willReturnCallback(function (string $key, mixed $value, ?int $ttl) use (&$values): bool {
            $values[$key] = $value;
            $this->assertSame(5, $ttl);

            return true;
        });

        $guard = new AnalyticsDuplicateGuard($cache);
        $key = $guard->key('identity-hash', '/private/path', AnalyticsEventSource::Initial);

        $this->assertStringNotContainsString('/private/path', $key);
        $this->assertTrue($guard->acquire($key));
        $this->assertFalse($guard->acquire($key));
    }

    public function test_tracking_decision_is_backed_enum_and_dtos_are_readonly(): void
    {
        $input = new PageViewInputData('/boards/free', AnalyticsEventSource::Initial, null, null, null);
        $context = new AnalyticsRequestContext('192.0.2.1', false, false, false, false, null, CarbonImmutable::now('UTC'));

        $this->assertSame('record', AnalyticsTrackingDecision::Record->value);
        $this->assertTrue((new \ReflectionClass($input))->isReadOnly());
        $this->assertTrue((new \ReflectionClass($context))->isReadOnly());
    }
}

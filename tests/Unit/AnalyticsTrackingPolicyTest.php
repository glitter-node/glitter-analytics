<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\DTO\AnalyticsRequestContext;
use Modules\Glitter\Analytics\DTO\PageViewInputData;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Enums\AnalyticsTrackingDecision;
use Modules\Glitter\Analytics\Services\AnalyticsTrackingPolicy;
use PHPUnit\Framework\TestCase;

class AnalyticsTrackingPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['Enums/AnalyticsEventSource.php', 'Enums/AnalyticsTrackingDecision.php', 'DTO/PageViewInputData.php', 'DTO/AnalyticsRequestContext.php', 'Services/AnalyticsTrackingPolicy.php'] as $file) {
            require_once dirname(__DIR__, 2).'/src/'.$file;
        }
    }

    public function test_policy_decisions_and_boundary_paths(): void
    {
        $policy = new AnalyticsTrackingPolicy;
        $settings = ['enabled' => true, 'respect_do_not_track' => true, 'exclude_bots' => true, 'exclude_paths' => ['/admin/*', '/api/*', '/build/*']];
        $input = fn (string $path = '/boards/free') => new PageViewInputData($path, AnalyticsEventSource::Initial, null, null, null);
        $context = fn (bool $dnt = false, bool $gpc = false, bool $prefetch = false, bool $bot = false) => new AnalyticsRequestContext('192.0.2.1', $dnt, $gpc, $prefetch, $bot, null, CarbonImmutable::now('UTC'));

        $this->assertSame(AnalyticsTrackingDecision::Disabled, $policy->decide($input(), $context(), [...$settings, 'enabled' => false]));
        $this->assertSame(AnalyticsTrackingDecision::DoNotTrack, $policy->decide($input(), $context(dnt: true), $settings));
        $this->assertSame(AnalyticsTrackingDecision::DoNotTrack, $policy->decide($input(), $context(gpc: true), $settings));
        $this->assertSame(AnalyticsTrackingDecision::Prefetch, $policy->decide($input(), $context(prefetch: true), $settings));
        $this->assertSame(AnalyticsTrackingDecision::Bot, $policy->decide($input(), $context(bot: true), $settings));
        $this->assertSame(AnalyticsTrackingDecision::Record, $policy->decide($input(), $context(bot: true), [...$settings, 'exclude_bots' => false]));
        $this->assertSame(AnalyticsTrackingDecision::ExcludedPath, $policy->decide($input('/admin'), $context(), $settings));
        $this->assertSame(AnalyticsTrackingDecision::ExcludedPath, $policy->decide($input('/admin/users'), $context(), $settings));
        $this->assertSame(AnalyticsTrackingDecision::Record, $policy->decide($input('/administrator'), $context(), $settings));
        $this->assertSame(AnalyticsTrackingDecision::Record, $policy->decide($input(), $context(), $settings));
    }
}

<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\DTO\CreateAnalyticsEventData;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Enums\AnalyticsEventType;
use PHPUnit\Framework\TestCase;
use ValueError;

class AnalyticsEventDomainTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2).'/src/Enums/AnalyticsEventType.php';
        require_once dirname(__DIR__, 2).'/src/Enums/AnalyticsEventSource.php';
        require_once dirname(__DIR__, 2).'/src/DTO/CreateAnalyticsEventData.php';
    }

    public function test_enum_values_and_invalid_values(): void
    {
        $this->assertSame(['page_view'], AnalyticsEventType::values());
        $this->assertSame(['initial', 'spa'], AnalyticsEventSource::values());
        $this->expectException(ValueError::class);
        AnalyticsEventType::from('unknown');
    }

    public function test_readonly_dto_preserves_types_and_nullable_fields(): void
    {
        $occurredAt = CarbonImmutable::parse('2026-07-14 00:00:00', 'UTC');
        $data = new CreateAnalyticsEventData(
            AnalyticsEventType::PageView,
            AnalyticsEventSource::Initial,
            $occurredAt,
            '/safe-path',
        );
        $array = $data->toArray();

        $this->assertSame('page_view', $array['event_type']);
        $this->assertSame('initial', $array['source']);
        $this->assertSame($occurredAt, $array['occurred_at']);
        $this->assertNull($array['user_id']);
        $this->assertNull($array['ip_hash']);
        $this->assertTrue((new \ReflectionClass($data))->isReadOnly());
    }
}

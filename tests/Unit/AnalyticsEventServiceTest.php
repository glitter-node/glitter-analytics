<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\DTO\CreateAnalyticsEventData;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Enums\AnalyticsEventType;
use Modules\Glitter\Analytics\Models\AnalyticsEvent;
use Modules\Glitter\Analytics\Repositories\Contracts\AnalyticsEventRepositoryInterface;
use Modules\Glitter\Analytics\Services\AnalyticsEventService;
use PHPUnit\Framework\TestCase;

class AnalyticsEventServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $base = dirname(__DIR__, 2);
        require_once $base.'/src/Enums/AnalyticsEventType.php';
        require_once $base.'/src/Enums/AnalyticsEventSource.php';
        require_once $base.'/src/DTO/CreateAnalyticsEventData.php';
        require_once $base.'/src/Models/AnalyticsEvent.php';
        require_once $base.'/src/Repositories/Contracts/AnalyticsEventRepositoryInterface.php';
        require_once $base.'/src/Services/AnalyticsEventService.php';
    }

    public function test_record_delegates_to_repository_interface(): void
    {
        $data = new CreateAnalyticsEventData(
            AnalyticsEventType::PageView,
            AnalyticsEventSource::Spa,
            CarbonImmutable::now('UTC'),
            '/safe-path',
        );
        $event = new AnalyticsEvent;
        $repository = $this->createMock(AnalyticsEventRepositoryInterface::class);
        $repository->expects($this->once())->method('create')->with($this->identicalTo($data))->willReturn($event);

        $this->assertSame($event, (new AnalyticsEventService($repository))->record($data));
    }
}

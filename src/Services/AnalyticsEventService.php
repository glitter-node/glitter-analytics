<?php

namespace Modules\Glitter\Analytics\Services;

use Modules\Glitter\Analytics\DTO\CreateAnalyticsEventData;
use Modules\Glitter\Analytics\Models\AnalyticsEvent;
use Modules\Glitter\Analytics\Repositories\Contracts\AnalyticsEventRepositoryInterface;

class AnalyticsEventService
{
    public function __construct(private AnalyticsEventRepositoryInterface $events) {}

    public function record(CreateAnalyticsEventData $data): AnalyticsEvent
    {
        return $this->events->create($data);
    }
}

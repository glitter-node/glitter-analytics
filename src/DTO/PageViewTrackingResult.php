<?php

namespace Modules\Glitter\Analytics\DTO;

use Modules\Glitter\Analytics\Enums\AnalyticsTrackingDecision;

final readonly class PageViewTrackingResult
{
    public function __construct(
        public bool $recorded,
        public AnalyticsTrackingDecision $decision,
    ) {}
}

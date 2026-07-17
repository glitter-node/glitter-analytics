<?php

namespace Modules\Glitter\Analytics\DTO;

use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;

final readonly class PageViewInputData
{
    public function __construct(
        public string $path,
        public AnalyticsEventSource $source,
        public ?string $referrerHost,
        public ?string $referrerPath,
        public ?string $locale,
        public ?string $visitorId = null,
        public ?string $sessionId = null,
    ) {}
}

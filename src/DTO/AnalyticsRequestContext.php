<?php

namespace Modules\Glitter\Analytics\DTO;

use Carbon\CarbonImmutable;

final readonly class AnalyticsRequestContext
{
    public function __construct(
        public ?string $ip,
        public bool $doNotTrack,
        public bool $globalPrivacyControl,
        public bool $prefetch,
        public bool $bot,
        public ?int $userId,
        public CarbonImmutable $receivedAt,
    ) {}
}

<?php

namespace Modules\Glitter\Analytics\DTO;

use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Enums\AnalyticsEventType;

final readonly class CreateAnalyticsEventData
{
    public function __construct(
        public AnalyticsEventType $eventType,
        public AnalyticsEventSource $source,
        public CarbonImmutable $occurredAt,
        public string $path,
        public ?string $routeName = null,
        public ?string $referrerHost = null,
        public ?string $referrerPath = null,
        public ?int $userId = null,
        public ?string $visitorHash = null,
        public ?string $sessionHash = null,
        public ?string $ipHash = null,
        public ?string $locale = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'event_type' => $this->eventType->value,
            'source' => $this->source->value,
            'occurred_at' => $this->occurredAt,
            'path' => $this->path,
            'route_name' => $this->routeName,
            'referrer_host' => $this->referrerHost,
            'referrer_path' => $this->referrerPath,
            'user_id' => $this->userId,
            'visitor_hash' => $this->visitorHash,
            'session_hash' => $this->sessionHash,
            'ip_hash' => $this->ipHash,
            'locale' => $this->locale,
        ];
    }
}

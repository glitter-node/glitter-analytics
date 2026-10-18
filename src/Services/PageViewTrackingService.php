<?php

namespace Modules\Glitter\Analytics\Services;

use Modules\Glitter\Analytics\DTO\AnalyticsRequestContext;
use Modules\Glitter\Analytics\DTO\CreateAnalyticsEventData;
use Modules\Glitter\Analytics\DTO\PageViewInputData;
use Modules\Glitter\Analytics\DTO\PageViewTrackingResult;
use Modules\Glitter\Analytics\Enums\AnalyticsEventType;
use Modules\Glitter\Analytics\Enums\AnalyticsTrackingDecision;

class PageViewTrackingService
{
    public function __construct(
        private AnalyticsSettingsService $settings,
        private AnalyticsTrackingPolicy $policy,
        private AnalyticsIdentityService $identity,
        private AnalyticsDuplicateGuard $duplicates,
        private AnalyticsEventService $events,
        private AnalyticsRealtimeService $realtime,
) {}

    public function track(PageViewInputData $input, AnalyticsRequestContext $context): PageViewTrackingResult
    {
        $settings = $this->settings->get();
        $decision = $this->policy->decide($input, $context, $settings);
        if ($decision !== AnalyticsTrackingDecision::Record) {
            return new PageViewTrackingResult(false, $decision);
        }

        $ipHash = $this->identity->ipHash($context->ip, (string) ($settings['ip_anonymization'] ?? 'hash'));
        $visitorHash = $this->identity->visitorHash($input->visitorId);
        $sessionHash = $this->identity->sessionHash($input->sessionId);
        $duplicateIdentity = $sessionHash ?? $visitorHash ?? ($context->userId === null ? null : hash('sha256', 'user:'.$context->userId)) ?? $ipHash;
        $duplicateKey = $this->duplicates->key($duplicateIdentity, $input->path, $input->source);
        if (! $this->duplicates->acquire($duplicateKey)) {
            return new PageViewTrackingResult(false, AnalyticsTrackingDecision::Duplicate);
        }

        $this->events->record(new CreateAnalyticsEventData(
            eventType: AnalyticsEventType::PageView,
            source: $input->source,
            occurredAt: $context->receivedAt,
            path: $input->path,
            routeName: null,
            referrerHost: $input->referrerHost,
            referrerPath: $input->referrerPath,
            userId: $context->userId,
            visitorHash: $visitorHash,
            sessionHash: $sessionHash,
            ipHash: $ipHash,
            locale: $input->locale,
        ));

        $this->realtime->broadcastDashboard();

        return new PageViewTrackingResult(true, AnalyticsTrackingDecision::Record);
    }
}

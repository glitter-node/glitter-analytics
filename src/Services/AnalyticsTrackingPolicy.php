<?php

namespace Modules\Glitter\Analytics\Services;

use Illuminate\Support\Str;
use Modules\Glitter\Analytics\DTO\AnalyticsRequestContext;
use Modules\Glitter\Analytics\DTO\PageViewInputData;
use Modules\Glitter\Analytics\Enums\AnalyticsTrackingDecision;

class AnalyticsTrackingPolicy
{
    public function decide(PageViewInputData $input, AnalyticsRequestContext $context, array $settings): AnalyticsTrackingDecision
    {
        if (! (bool) ($settings['enabled'] ?? true)) {
            return AnalyticsTrackingDecision::Disabled;
        }
        if ((bool) ($settings['respect_do_not_track'] ?? true) && ($context->doNotTrack || $context->globalPrivacyControl)) {
            return AnalyticsTrackingDecision::DoNotTrack;
        }
        if ($context->prefetch) {
            return AnalyticsTrackingDecision::Prefetch;
        }
        if ((bool) ($settings['exclude_bots'] ?? true) && $context->bot) {
            return AnalyticsTrackingDecision::Bot;
        }
        foreach ((array) ($settings['exclude_paths'] ?? []) as $pattern) {
            $pattern = rtrim((string) $pattern, '*');
            if ($pattern !== '' && ($input->path === rtrim($pattern, '/') || Str::is($pattern.'*', $input->path))) {
                return AnalyticsTrackingDecision::ExcludedPath;
            }
        }

        return AnalyticsTrackingDecision::Record;
    }
}

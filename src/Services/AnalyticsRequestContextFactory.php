<?php

namespace Modules\Glitter\Analytics\Services;

use App\Seo\BotDetector;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Modules\Glitter\Analytics\DTO\AnalyticsRequestContext;

class AnalyticsRequestContextFactory
{
    public function __construct(private BotDetector $botDetector) {}

    public function fromRequest(Request $request): AnalyticsRequestContext
    {
        $purpose = strtolower((string) ($request->header('Sec-Purpose') ?: $request->header('Purpose')));

        return new AnalyticsRequestContext(
            ip: $request->ip(),
            doNotTrack: $request->header('DNT') === '1',
            globalPrivacyControl: $request->header('Sec-GPC') === '1',
            prefetch: str_contains($purpose, 'prefetch') || strtolower((string) $request->header('X-Moz')) === 'prefetch',
            bot: $this->botDetector->isBot($request),
            userId: $request->user('sanctum')?->id,
            receivedAt: CarbonImmutable::now('UTC'),
        );
    }
}

<?php

namespace Modules\Glitter\Analytics\Http\Controllers\Api\Public;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\PublicBaseController;
use Illuminate\Http\JsonResponse;
use Modules\Glitter\Analytics\DTO\PageViewInputData;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Http\Requests\StorePageViewRequest;
use Modules\Glitter\Analytics\Http\Resources\PageViewTrackingResource;
use Modules\Glitter\Analytics\Services\AnalyticsRequestContextFactory;
use Modules\Glitter\Analytics\Services\AnalyticsRequestNormalizer;
use Modules\Glitter\Analytics\Services\PageViewTrackingService;

class PageViewController extends PublicBaseController
{
    public function __construct(private PageViewTrackingService $tracking, private AnalyticsRequestNormalizer $normalizer, private AnalyticsRequestContextFactory $contextFactory)
    {
        parent::__construct();
    }

    public function store(StorePageViewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $referrer = $this->normalizer->referrer($validated['referrer'] ?? null);
        $result = $this->tracking->track(new PageViewInputData(
            $this->normalizer->path($validated['path']), AnalyticsEventSource::from($validated['source']),
            $referrer['host'] ?? null, $referrer['path'] ?? null, $validated['locale'] ?? null,
            $validated['visitor_id'] ?? null, $validated['session_id'] ?? null,
        ), $this->contextFactory->fromRequest($request));

        return ResponseHelper::moduleSuccessWithResource('glitter-analytics', 'messages.pageview.accepted', new PageViewTrackingResource(['recorded' => $result->recorded]), 202);
    }
}

<?php

namespace Modules\Glitter\Analytics\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\AdminBaseController;
use Illuminate\Http\JsonResponse;
use Modules\Glitter\Analytics\Http\Requests\UpdateAnalyticsSettingsRequest;
use Modules\Glitter\Analytics\Http\Resources\AnalyticsSettingsResource;
use Modules\Glitter\Analytics\Services\AnalyticsSettingsService;

class AnalyticsSettingsController extends AdminBaseController
{
    public function __construct(private AnalyticsSettingsService $settings) {}

    public function show(): JsonResponse
    {
        return ResponseHelper::moduleSuccessWithResource('glitter-analytics', 'messages.settings.retrieved', new AnalyticsSettingsResource(['settings' => $this->settings->get()]));
    }

    public function update(UpdateAnalyticsSettingsRequest $request): JsonResponse
    {
        return ResponseHelper::moduleSuccessWithResource('glitter-analytics', 'messages.settings.updated', new AnalyticsSettingsResource(['settings' => $this->settings->update($request->validated())]));
    }
}

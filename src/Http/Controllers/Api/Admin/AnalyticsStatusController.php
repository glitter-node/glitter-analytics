<?php

namespace Modules\Glitter\Analytics\Http\Controllers\Api\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\AdminBaseController;
use Illuminate\Http\JsonResponse;
use Modules\Glitter\Analytics\Http\Resources\AnalyticsStatusResource;

class AnalyticsStatusController extends AdminBaseController
{
    public function show(): JsonResponse
    {
        $resource = new AnalyticsStatusResource([
            'identifier' => 'glitter-analytics',
            'version' => '0.7.0',
            'enabled' => (bool) module_setting('glitter-analytics', 'general.enabled', true),
            'status' => 'ready',
        ]);

        return ResponseHelper::moduleSuccessWithResource(
            'glitter-analytics',
            'messages.status_retrieved',
            $resource,
        );
    }
}

<?php

namespace Modules\Glitter\Analytics\Services;

use App\Extension\HookManager;
use Illuminate\Http\Request;
use Modules\Glitter\Analytics\Http\Resources\AnalyticsDashboardResource;
use Illuminate\Support\Facades\Log;

class AnalyticsRealtimeService
{
    public function __construct(private AnalyticsQueryService $queries) {}

    public function broadcastDashboard(): void
    {
        if (in_array(config("broadcasting.default"), ["null", "log", null], true)) { return; }
        try {
            $payload = (new AnalyticsDashboardResource($this->queries->dashboard()))->resolve(Request::create("/"));
        HookManager::broadcast("module.glitter-analytics.dashboard", "dashboard.updated", ["data" => $payload]);
        } catch (\Throwable $e) {
            Log::warning("Analytics realtime dashboard broadcast skipped", ["exception" => $e::class]);
        }
    }
}

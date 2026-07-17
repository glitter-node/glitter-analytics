<?php

namespace Modules\Glitter\Analytics\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Glitter\Analytics\Repositories\Contracts\AnalyticsEventRepositoryInterface;
use Modules\Glitter\Analytics\Services\AnalyticsSettingsService;

class PruneAnalyticsEventsCommand extends Command
{
    protected $signature = "glitter-analytics:prune {--dry-run : Count expired events without deleting} {--chunk=1000 : Rows deleted per chunk}";
    protected $description = "Delete analytics events older than the configured retention period";

    public function handle(AnalyticsSettingsService $settings, AnalyticsEventRepositoryInterface $events): int
    {
        $days = (int) ($settings->get()["retention_days"] ?? 90);
        $chunk = max(100, min(10000, (int) $this->option("chunk")));
        $cutoff = CarbonImmutable::now("UTC")->subDays($days);
        if ($this->option("dry-run")) {
            $count = $events->countOlderThan($cutoff);
            $this->info("Analytics retention dry run: {$count} event(s) before {$cutoff->toIso8601String()}.");
            Log::info("Analytics retention dry run", ["retention_days" => $days, "expired_count" => $count]);
            return self::SUCCESS;
        }
        $total = 0;
        do { $deleted = $events->deleteOlderThan($cutoff, $chunk); $total += $deleted; } while ($deleted === $chunk);
        $this->info("Analytics retention complete: {$total} event(s) deleted.");
        Log::info("Analytics retention complete", ["retention_days" => $days, "deleted_count" => $total]);
        return self::SUCCESS;
    }
}

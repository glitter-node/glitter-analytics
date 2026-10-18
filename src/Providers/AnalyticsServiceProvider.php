<?php

namespace Modules\Glitter\Analytics\Providers;

use App\Extension\BaseModuleServiceProvider;
use Modules\Glitter\Analytics\Repositories\AnalyticsEventRepository;
use Modules\Glitter\Analytics\Repositories\Contracts\AnalyticsEventRepositoryInterface;
use Modules\Glitter\Analytics\Services\AnalyticsDuplicateGuard;
use Modules\Glitter\Analytics\Console\Commands\PruneAnalyticsEventsCommand;

class AnalyticsServiceProvider extends BaseModuleServiceProvider
{
    protected array $commands = [PruneAnalyticsEventsCommand::class];
    protected string $moduleIdentifier = 'glitter-analytics';

    /**  array<class-string, class-string> */
    protected array $repositories = [
        AnalyticsEventRepositoryInterface::class => AnalyticsEventRepository::class,
    ];

    /**  array<int, class-string> */
    protected array $cacheServices = [AnalyticsDuplicateGuard::class];
    public function boot(): void
    {
        parent::boot();
        if ($this->app->runningInConsole()) { $this->commands($this->commands); }
    }
}

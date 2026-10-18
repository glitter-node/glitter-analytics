<?php

namespace Modules\Glitter\Analytics\Services;

use App\Services\ModuleSettingsService;

class AnalyticsSettingsService
{
    public function __construct(private ModuleSettingsService $settings) {}

    public function get(): array
    {
        return $this->settings->get('glitter-analytics', 'general', []);
    }

    public function update(array $settings): array
    {
        $settings['exclude_paths'] = array_values(array_unique(array_filter(array_map(static fn (string $path): string => trim($path), $settings['exclude_paths']), static fn (string $path): bool => $path !== '')));
        if (! $this->settings->save('glitter-analytics', ['general' => $settings])) {
            throw new \RuntimeException('Unable to save analytics settings.');
        }
        $this->settings->clearCache('glitter-analytics');

        return $this->get();
    }
}

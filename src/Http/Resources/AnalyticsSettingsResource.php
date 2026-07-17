<?php

namespace Modules\Glitter\Analytics\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class AnalyticsSettingsResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        $settings = $this->getValue('settings', []);
        $settings['exclude_paths_text'] = implode("\n", $settings['exclude_paths'] ?? []);

        return ['settings' => $settings, 'options' => ['ip_anonymization' => [
            ['value' => 'hash', 'label' => __('glitter-analytics::messages.settings.options.hash')],
            ['value' => 'mask', 'label' => __('glitter-analytics::messages.settings.options.mask')],
            ['value' => 'none', 'label' => __('glitter-analytics::messages.settings.options.none')]]]];
    }
}

<?php

namespace Modules\Glitter\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnalyticsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<int, string> */
    public static function normalizeExcludePathsText(string $value): array
    {
        $lines = preg_split('/\R/u', $value) ?: [];

        return array_values(array_unique(array_filter(
            array_map(static fn (string $path): string => trim($path), $lines),
            static fn (string $path): bool => $path !== '',
        )));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('exclude_paths_text')) {
            $this->merge([
                'exclude_paths' => self::normalizeExcludePathsText((string) $this->input('exclude_paths_text')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'ip_anonymization' => ['required', 'string', Rule::in(['hash', 'mask', 'none'])],
            'exclude_bots' => ['required', 'boolean'],
            'exclude_paths_text' => ['sometimes', 'string'],
            'exclude_paths' => ['required', 'array', 'max:100'],
            'exclude_paths.*' => ['required', 'string', 'max:512'],
            'session_timeout_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'respect_do_not_track' => ['required', 'boolean'],
        ];
    }
}

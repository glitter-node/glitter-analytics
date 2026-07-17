<?php

namespace Modules\Glitter\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Services\AnalyticsRequestNormalizer;

class StorePageViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $normalizer = app(AnalyticsRequestNormalizer::class);

        return [
            'path' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail) use ($normalizer): void {
                try {
                    $normalizer->path((string) $value);
                } catch (\InvalidArgumentException) {
                    $fail(__('glitter-analytics::validation.pageview.path'));
                }
            }],
            'referrer' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail) use ($normalizer): void {
                try {
                    $normalizer->referrer(is_string($value) ? $value : null);
                } catch (\InvalidArgumentException) {
                    $fail(__('glitter-analytics::validation.pageview.referrer'));
                }
            }],
            'source' => ['required', Rule::enum(AnalyticsEventSource::class)],
            'locale' => ['nullable', 'string', Rule::in(config('app.supported_locales', []))],
            'visitor_id' => ['nullable', 'string', 'uuid:4'],
            'session_id' => ['nullable', 'string', 'uuid:4'],
            'occurred_at' => ['prohibited'], 'visitor_hash' => ['prohibited'], 'session_hash' => ['prohibited'],
            'ip_hash' => ['prohibited'], 'user_id' => ['prohibited'], 'event_type' => ['prohibited'],
            'user_agent' => ['prohibited'], 'cookie' => ['prohibited'], 'token' => ['prohibited'],
        ];
    }
}

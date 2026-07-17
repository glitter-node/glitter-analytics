<?php

namespace Modules\Glitter\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Glitter\Analytics\Enums\AnalyticsEventSource;
use Modules\Glitter\Analytics\Enums\AnalyticsEventType;

class AnalyticsEvent extends Model
{
    protected $table = 'analytics_events';

    protected $fillable = [
        'event_type', 'source', 'occurred_at', 'path', 'route_name',
        'referrer_host', 'referrer_path', 'user_id', 'visitor_hash',
        'session_hash', 'ip_hash', 'locale',
    ];

    protected $hidden = ['visitor_hash', 'session_hash', 'ip_hash'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'event_type' => AnalyticsEventType::class,
            'source' => AnalyticsEventSource::class,
            'occurred_at' => 'immutable_datetime',
            'user_id' => 'integer',
        ];
    }
}

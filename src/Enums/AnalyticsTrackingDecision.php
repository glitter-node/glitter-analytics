<?php

namespace Modules\Glitter\Analytics\Enums;

enum AnalyticsTrackingDecision: string
{
    case Record = 'record';
    case Disabled = 'disabled';
    case DoNotTrack = 'do_not_track';
    case Bot = 'bot';
    case ExcludedPath = 'excluded_path';
    case Prefetch = 'prefetch';
    case Duplicate = 'duplicate';
}

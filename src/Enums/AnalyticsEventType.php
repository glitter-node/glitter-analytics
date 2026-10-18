<?php

namespace Modules\Glitter\Analytics\Enums;

enum AnalyticsEventType: string
{
    case PageView = 'page_view';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

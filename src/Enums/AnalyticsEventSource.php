<?php

namespace Modules\Glitter\Analytics\Enums;

enum AnalyticsEventSource: string
{
    case Initial = 'initial';
    case Spa = 'spa';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

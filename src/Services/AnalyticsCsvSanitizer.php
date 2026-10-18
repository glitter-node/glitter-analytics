<?php

namespace Modules\Glitter\Analytics\Services;

final class AnalyticsCsvSanitizer
{
    public function cell(string|int|bool|null $value): string
    {
        $cell = (string) $value;
        if ($cell !== "" && preg_match("/^[=+\-@\t\r]/u", $cell) === 1) {
            return chr(39).$cell;
        }
        return $cell;
    }

    public function row(array $values): array
    {
        return array_map(fn (mixed $value): string => $this->cell(is_scalar($value) || $value === null ? $value : ""), $values);
    }
}

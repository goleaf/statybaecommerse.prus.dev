<?php

declare(strict_types=1);

namespace App\Support;

final class DateRange
{
    /**
     * @return array{0: string|null, 1: string|null}
     */
    public static function extract(array $data, string $startKey, ?string $endKey = null): array
    {
        $value = $data[$startKey] ?? null;

        if (is_array($value)) {
            return [
                $value[0] ?? null,
                $value[1] ?? ($endKey !== null ? ($data[$endKey] ?? null) : null),
            ];
        }

        return [
            $value,
            $endKey !== null ? ($data[$endKey] ?? null) : null,
        ];
    }
}

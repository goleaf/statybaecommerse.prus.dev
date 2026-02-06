<?php

declare(strict_types=1);

namespace App\Support\ImportExport;

final class ProgressCounter
{
    public static function normalizeTotal(int $total): int
    {
        return max(0, $total);
    }

    public static function normalizeProcessed(int $processed, int $total): int
    {
        return max(0, min($processed, self::normalizeTotal($total)));
    }

    public static function normalizeSuccessful(int $successful, int $processed, int $total): int
    {
        return max(0, min($successful, self::normalizeProcessed($processed, $total)));
    }

    public static function failedRows(int $processed, int $successful, int $total): int
    {
        $safeProcessed = self::normalizeProcessed($processed, $total);
        $safeSuccessful = self::normalizeSuccessful($successful, $processed, $total);

        return max(0, $safeProcessed - $safeSuccessful);
    }

    public static function percent(int $processed, int $total): int
    {
        $safeTotal = self::normalizeTotal($total);

        if ($safeTotal === 0) {
            return 0;
        }

        $safeProcessed = self::normalizeProcessed($processed, $safeTotal);

        return min(100, (int) floor(($safeProcessed / $safeTotal) * 100));
    }
}

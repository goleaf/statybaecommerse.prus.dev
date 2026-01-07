<?php

namespace Coolsam\Flatpickr;

class FilamentFlatpickr
{
    public static function getPackageName(): string
    {
        return 'coolsam/flatpickr';
    }

    public static function getBool(mixed $boolValue): bool
    {
        return filter_var($boolValue, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getInt(mixed $intValue): int
    {
        $res = filter_var($intValue, FILTER_VALIDATE_INT);
        if ($res === false) {
            return 0;
        }

        return $res;
    }
}

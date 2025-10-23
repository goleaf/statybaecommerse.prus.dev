<?php

declare(strict_types=1);

namespace App\Support;

use DateTime;
use DateTimeInterface;
use Illuminate\Support\Carbon;

final class DateParser
{
    /**
     * Parse a mixed value into a Carbon instance or return null when invalid.
     * Optionally accepts a simple fallback keyword such as 'today'.
     */
    public static function parse(mixed $value, ?string $fallback = null): ?Carbon
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                // fallthrough to fallback handling
            }
        }

        if ($fallback === 'today') {
            return Carbon::now();
        }

        return null;
    }

    /**
     * Create a Carbon instance from a formatted string or return null when invalid.
     */
    public static function fromFormat(string $format, string $value): ?Carbon
    {
        $dt = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();

        if ($dt === false || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return null;
        }

        return Carbon::instance($dt);
    }
}


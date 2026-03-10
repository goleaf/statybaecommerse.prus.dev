<?php

declare(strict_types=1);

namespace App\Support\Encoding;

final class MojibakeFixer
{
    private const SUSPICIOUS_PATTERN = '/(?:[\x{00C2}\x{00C3}\x{00C4}\x{00C5}][\x{0080}-\x{00FF}\x{0152}\x{0153}\x{0160}\x{0161}\x{0178}\x{017D}\x{017E}\x{0192}\x{02C6}\x{02DC}\x{2013}\x{2014}\x{2018}-\x{201E}\x{2020}-\x{2022}\x{2026}\x{2030}\x{2039}\x{203A}\x{20AC}\x{2122}])/u';

    public static function repair(?string $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (preg_match(self::SUSPICIOUS_PATTERN, $value) !== 1) {
            return $value;
        }

        $repaired = iconv('UTF-8', 'Windows-1252//IGNORE', $value);

        if (! is_string($repaired) || $repaired === '') {
            return $value;
        }

        return preg_match(self::SUSPICIOUS_PATTERN, $repaired) === 1 ? $value : $repaired;
    }
}

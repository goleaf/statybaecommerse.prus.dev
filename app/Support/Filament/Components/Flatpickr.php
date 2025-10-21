<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Coolsam\Flatpickr\Forms\Components\Flatpickr as BaseFlatpickr;

final class Flatpickr
{
    private const DATE_FORMAT = 'Y-m-d';

    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    private const DATE_TIME_NO_SECONDS_FORMAT = 'Y-m-d H:i';

    private const TIME_FORMAT = 'H:i';

    private function __construct() {}

    public static function makeDate(
        string $name,
        string $displayFormat = self::DATE_FORMAT,
        string $format = self::DATE_FORMAT,
    ): BaseFlatpickr {
        $component = self::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->time(false);

        return $component;
    }

    public static function makeDateTime(
        string $name,
        bool $withSeconds = true,
        ?string $displayFormat = null,
        ?string $format = null,
    ): BaseFlatpickr {
        $format ??= $withSeconds ? self::DATE_TIME_FORMAT : self::DATE_TIME_NO_SECONDS_FORMAT;
        $displayFormat ??= $format;

        $component = self::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->time(true);

        return $component->seconds($withSeconds);
    }

    public static function makeTime(
        string $name,
        string $displayFormat = self::TIME_FORMAT,
        string $format = self::TIME_FORMAT,
    ): BaseFlatpickr {
        return self::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->date(false)
            ->time(true)
            ->seconds(false);
    }

    public static function makeRange(
        string $name,
        bool $withTime = false,
        ?string $displayFormat = null,
        ?string $format = null,
    ): BaseFlatpickr {
        $format ??= $withTime ? self::DATE_TIME_FORMAT : self::DATE_FORMAT;
        $displayFormat ??= $format;

        $component = self::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->rangePicker();

        return $withTime ? $component->time(true) : $component->time(false);
    }

    private static function make(string $name): BaseFlatpickr
    {
        return BaseFlatpickr::make($name);
    }
}

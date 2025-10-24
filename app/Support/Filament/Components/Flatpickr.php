<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TimePicker;

final class Flatpickr
{
    private const DATE_FORMAT = 'Y-m-d';

    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    private const DATE_TIME_NO_SECONDS_FORMAT = 'Y-m-d H:i';

    private const TIME_FORMAT = 'H:i';

    private function __construct() {}

    /**
     * Build a Filament date picker that mirrors the legacy Flatpickr date behaviour.
     */
    public static function makeDate(
        string $name,
        string $displayFormat = self::DATE_FORMAT,
        string $format = self::DATE_FORMAT,
    ): DatePicker {
        $component = DatePicker::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->seconds(false)
            ->time(false);

        // Force the Alpine-powered widget instead of the browser native picker for consistency.
        return $component->native(false);
    }

    /**
     * Provide a consistent datetime picker that respects second precision toggles.
     */
    public static function makeDateTime(
        string $name,
        bool $withSeconds = true,
        ?string $displayFormat = null,
        ?string $format = null,
    ): DateTimePicker {
        $format ??= $withSeconds ? self::DATE_TIME_FORMAT : self::DATE_TIME_NO_SECONDS_FORMAT;
        $displayFormat ??= $format;

        $component = DateTimePicker::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->time(true);

        // Respect second precision when explicitly requested while keeping a uniform UI widget.
        return $component
            ->seconds($withSeconds)
            ->native(false);
    }

    public static function makeTime(
        string $name,
        string $displayFormat = self::TIME_FORMAT,
        string $format = self::TIME_FORMAT,
    ): TimePicker {
        // Time pickers stay focused on the time wheel only, replicating the Flatpickr configuration.
        return TimePicker::make($name)
            ->displayFormat($displayFormat)
            ->format($format)
            ->date(false)
            ->time(true)
            ->seconds(false)
            ->native(false);
    }

    public static function makeRange(
        string $name,
        bool $withTime = false,
        ?string $displayFormat = null,
        ?string $format = null,
    ): Group {
        $format ??= $withTime ? self::DATE_TIME_FORMAT : self::DATE_FORMAT;
        $displayFormat ??= $format;

        // Wrap two coordinated pickers in a schema group so filters receive the familiar ['start', 'end'] payload.
        return Group::make([
            $withTime
                ? DateTimePicker::make('start')
                    ->displayFormat($displayFormat)
                    ->format($format)
                    ->seconds(true)
                    ->native(false)
                : DatePicker::make('start')
                    ->displayFormat($displayFormat)
                    ->format($format)
                    ->seconds(false)
                    ->time(false)
                    ->native(false),
            $withTime
                ? DateTimePicker::make('end')
                    ->displayFormat($displayFormat)
                    ->format($format)
                    ->seconds(true)
                    ->native(false)
                : DatePicker::make('end')
                    ->displayFormat($displayFormat)
                    ->format($format)
                    ->seconds(false)
                    ->time(false)
                    ->native(false),
        ])
            ->columns(2)
            ->statePath($name);
    }
}

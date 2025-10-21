<?php

declare(strict_types=1);

namespace App\Support\Filament\Forms\Components;

use Coolsam\Flatpickr\Forms\Components\Flatpickr as BaseFlatpickr;

final class Flatpickr extends BaseFlatpickr
{
    public function asDate(string $displayFormat = 'Y-m-d', string $format = 'Y-m-d'): self
    {
        return $this
            ->displayFormat($displayFormat)
            ->format($format)
            ->time(false);
    }

    public function asDateRange(string $displayFormat = 'Y-m-d', string $format = 'Y-m-d'): self
    {
        return $this
            ->asDate($displayFormat, $format)
            ->rangePicker();
    }

    public function asDateTime(
        string $displayFormat = 'Y-m-d H:i',
        string $format = 'Y-m-d H:i:s',
        bool $withSeconds = false
    ): self {
        $display = $withSeconds && $displayFormat === 'Y-m-d H:i'
            ? 'Y-m-d H:i:s'
            : $displayFormat;

        return $this
            ->displayFormat($display)
            ->format($format)
            ->time(true)
            ->seconds($withSeconds);
    }

    public function asDateTimeRange(
        string $displayFormat = 'Y-m-d H:i',
        string $format = 'Y-m-d H:i:s',
        bool $withSeconds = false
    ): self {
        return $this
            ->asDateTime($displayFormat, $format, $withSeconds)
            ->rangePicker();
    }

    public function asTime(string $displayFormat = 'H:i', string $format = 'H:i', bool $withSeconds = false): self
    {
        $display = $withSeconds && $displayFormat === 'H:i'
            ? 'H:i:s'
            : $displayFormat;
        $valueFormat = $withSeconds && $format === 'H:i'
            ? 'H:i:s'
            : $format;

        return $this
            ->date(false)
            ->time(true)
            ->timePicker()
            ->seconds($withSeconds)
            ->displayFormat($display)
            ->format($valueFormat);
    }
}

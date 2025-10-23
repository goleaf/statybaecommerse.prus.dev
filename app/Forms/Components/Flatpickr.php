<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Coolsam\Flatpickr\Forms\Components\Flatpickr as BaseFlatpickr;

class Flatpickr extends BaseFlatpickr
{
    public function datePicker(string $displayFormat = 'Y-m-d', string $format = 'Y-m-d'): static
    {
        return $this
            ->date(true)
            ->time(false)
            ->displayFormat($displayFormat)
            ->format($format);
    }

    public function dateTimePicker(string $displayFormat = 'Y-m-d H:i', string $format = 'Y-m-d H:i:s'): static
    {
        return $this
            ->date(true)
            ->time(true)
            ->displayFormat($displayFormat)
            ->format($format);
    }

    public function timeOnly(string $displayFormat = 'H:i', string $format = 'H:i:s'): static
    {
        return $this
            ->timePicker()
            ->displayFormat($displayFormat)
            ->format($format);
    }

    public function dateRangePicker(string $displayFormat = 'Y-m-d', string $format = 'Y-m-d'): static
    {
        return $this
            ->date(true)
            ->time(false)
            ->rangePicker()
            ->displayFormat($displayFormat)
            ->format($format)
            ->conjunction(' to ');
    }

    public function dateTimeRangePicker(string $displayFormat = 'Y-m-d H:i', string $format = 'Y-m-d H:i:s'): static
    {
        return $this
            ->date(true)
            ->time(true)
            ->rangePicker()
            ->displayFormat($displayFormat)
            ->format($format)
            ->conjunction(' to ');
    }
}

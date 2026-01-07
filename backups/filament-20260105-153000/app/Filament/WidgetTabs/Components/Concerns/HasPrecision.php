<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components\Concerns;

trait HasPrecision
{
    protected int $precision = 0;

    public function precision(int $precision): static
    {
        $this->precision = $precision;

        return $this;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }
}

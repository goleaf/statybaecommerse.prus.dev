<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components\Concerns;

trait HasPercentage
{
    protected bool $isPercentage = false;

    protected int $percentagePrecision = 1;

    public function percentage(bool $condition = true): static
    {
        $this->isPercentage = $condition;

        return $this;
    }

    public function isPercentage(): bool
    {
        return $this->isPercentage;
    }

    public function percentagePrecision(int $precision): static
    {
        $this->percentagePrecision = $precision;

        return $this;
    }

    public function getPercentagePrecision(): int
    {
        return $this->percentagePrecision;
    }
}

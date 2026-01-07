<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Concerns;

use App\Support\DateRange;
use Illuminate\Support\Carbon;

trait ResolvesPageFilters
{
    /**
     * Provide a consistent array-backed snapshot of the current filter state so
     * analytics queries can safely read expected keys even before the modal is
     * opened by the operator.
     *
     * @return array<string, mixed>
     */
    protected function resolvePageFilters(): array
    {
        // Livewire keeps $pageFilters null until the first interaction, therefore
        // we normalise it to an empty array to prevent null offset notices.
        return $this->pageFilters ?? [];
    }

    /**
     * Fetch a scalar filter value while falling back to the provided default to
     * keep widget logic resilient when the filter form has not been submitted.
     */
    protected function resolveFilterValue(string $key, mixed $default = null): mixed
    {
        return $this->resolvePageFilters()[$key] ?? $default;
    }

    /**
     * Translate the stored filter payload into Carbon instances that cover the
     * active date range, defaulting to the trailing 30 days when unset so every
     * widget observes a predictable reporting window.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveDateRange(): array
    {
        $filters = $this->resolvePageFilters();

        [$rawStart, $rawEnd] = DateRange::extract($filters, 'startDate', 'endDate');

        $now = now();
        $startDate = $rawStart !== null
            ? Carbon::parse($rawStart)
            : $now->copy()->subDays(30);
        $endDate = $rawEnd !== null
            ? Carbon::parse($rawEnd)
            : $now;

        return [$startDate, $endDate];
    }
}

<?php

declare(strict_types=1);

namespace App\Support\FilamentCompat;

trait HasResizableColumn
{
    /**
     * Provide a minimal stub implementation for the resizable column concern when the vendor package is unavailable.
     *
     * @return array<int, string>
     */
    protected function getResizableColumns(): array
    {
        return [];
    }
}

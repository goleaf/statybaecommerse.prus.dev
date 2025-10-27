<?php

declare(strict_types=1);

namespace App\Support\FilamentCompat\Schemas\Components;

use Filament\Schemas\Components\Grid as BaseGrid;

/**
 * Exposes the nested schema components for compatibility with legacy metadata probes.
 */
final class Grid extends BaseGrid
{
    /**
     * @return array<mixed>
     */
    public function getComponents(): array
    {
        $components = $this->getDefaultChildComponents();

        if ($components instanceof \Filament\Schemas\Schema) {
            return $components->getComponents();
        }

        return is_array($components) ? array_values($components) : [];
    }
}

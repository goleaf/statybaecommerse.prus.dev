<?php

declare(strict_types=1);

namespace App\Support\FilamentCompat\Schemas\Components;

use Filament\Schemas\Components\Section as BaseSection;

/**
 * Temporary bridge so traversal logic expecting `getComponents()` continues to work
 * after migrating to the Filament v4 schema primitives.
 */
final class Section extends BaseSection
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

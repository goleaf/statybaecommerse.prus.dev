<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

/**
 * Simplified numeric input tailored for stock-quantity style fields.
 *
 * The legacy project referenced a bespoke Quantity component that no longer
 * ships with the codebase. This replacement extends Filament's TextInput so
 * existing resource definitions continue to operate without modification.
 */
final class Quantity extends TextInput
{
    public static function make(?string $name = null): static
    {
        /** @var static $component */
        $component = parent::make($name);

        // Apply a consistent set of sensible defaults so every quantity field
        // behaves like a non-negative integer input across the admin panel.
        return $component
            ->numeric()
            ->rule('integer')
            ->extraAttributes(['inputmode' => 'numeric'])
            ->minValue(0)
            ->step(1);
    }

    /**
     * Maintain backwards compatibility with the legacy `steps()` fluent call.
     */
    public function steps(int|float|string|null $step): static
    {
        // Delegate to Filament's native implementation to keep validation and
        // HTML attribute handling in sync with upstream behaviour.
        return $this->step($step);
    }
}

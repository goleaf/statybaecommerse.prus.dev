<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Novadaemon\FilamentCombobox\Combobox as BaseCombobox;

/**
 * Shared wrapper around the vendor Combobox component to centralize
 * the defaults we want across Filament resources.
 */
class Combobox extends BaseCombobox
{
    /**
     * Bootstrap sensible defaults immediately after instantiation.
     */
    public static function make(?string $name = null): static
    {
        /** @var static $component */
        $component = parent::make($name);

        return $component->applyBaseDefaults();
    }

    /**
     * Ensure the select renders with the modern combobox experience.
     */
    protected function applyBaseDefaults(): static
    {
        // The wrapper always forces the JavaScript-powered dropdown.
        $this->native(false);

        return $this;
    }

    /**
     * Apply the common relationship behaviours used throughout the admin.
     */
    public function relationshipDefaults(bool $shouldPreload = true, bool $shouldEnableSearchBox = true): static
    {
        if ($shouldPreload) {
            $this->preload();
        }

        if ($shouldEnableSearchBox) {
            $this->boxSearchs();
        }

        // Searching is universally expected when the combobox is displayed.
        return $this->searchable();
    }
}

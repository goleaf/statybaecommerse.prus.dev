<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Illuminate\Support\Facades\Lang;
use Novadaemon\FilamentCombobox\Combobox as BaseCombobox;

/**
 * Application wrapper for the Novadaemon combobox component.
 */
final class Combobox extends BaseCombobox
{
    /**
     * Apply the default application-wide combobox settings.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Surface the dual search inputs by default so operators can filter quickly.
        $this->boxSearchs();

        // Provide a generous default height that works well for most admin layouts.
        $this->height('360px');
    }

    /**
     * Configure translated column headers with an optional plain-text fallback.
     */
    public function translatedLabels(
        string $availableKey,
        string $selectedKey,
        ?string $availableFallback = null,
        ?string $selectedFallback = null,
    ): static {
        $this->optionsLabel($this->resolveLabel($availableKey, $availableFallback));
        $this->selectedLabel($this->resolveLabel($selectedKey, $selectedFallback));

        return $this;
    }

    /**
     * Resolve a translation key while gracefully falling back to a provided default string.
     */
    private function resolveLabel(string $key, ?string $fallback): string
    {
        if (Lang::has($key)) {
            return __($key);
        }

        return $fallback ?? $key;
    }
}

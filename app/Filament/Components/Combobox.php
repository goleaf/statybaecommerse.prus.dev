<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Closure;
use Novadaemon\FilamentCombobox\Combobox as BaseCombobox;

/**
 * Application wrapper around the Filament combobox plugin.
 *
 * Centralises default configuration and exposes helper shortcuts for
 * consistent labelling across admin resources.
 */
final class Combobox extends BaseCombobox
{
    public static function make(?string $name = null): static
    {
        $component = parent::make($name);

        // Apply the shared UX defaults immediately so every combobox feels consistent.
        return $component
            ->boxSearchs(true)
            ->height('340px')
            ->native(false)
            ->preload();
    }

    /**
     * Apply static column labels without repeating boilerplate closures.
     */
    public function withLabels(string $optionsLabel, string $selectedLabel): static
    {
        // Delegate to the closure-based helper so late binding remains possible if needed.
        return $this->withLabelClosures(
            static fn (): string => $optionsLabel,
            static fn (): string => $selectedLabel,
        );
    }

    /**
     * Resolve the dual-list headers from translation keys for localisation.
     */
    public function withLocalizedLabels(string $optionsKey, string $selectedKey): static
    {
        // Wrap the translation calls in closures so the current locale is honoured at render time.
        return $this->withLabelClosures(
            static fn (): string => __($optionsKey),
            static fn (): string => __($selectedKey),
        );
    }

    /**
     * Accept arbitrary label resolver callbacks for advanced use cases.
     *
     * @param Closure():string $optionsResolver  Lazily resolves the available column heading.
     * @param Closure():string $selectedResolver Lazily resolves the selected column heading.
     */
    public function withLabelClosures(Closure $optionsResolver, Closure $selectedResolver): static
    {
        // Let the underlying component manage the callbacks, but keep the fluent chain intact.
        return $this
            ->optionsLabel($optionsResolver)
            ->selectedLabel($selectedResolver);
    }
}

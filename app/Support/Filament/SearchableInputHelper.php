<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

/**
 * Centralises Filament searchable input hydration so state mirrors the
 * documented payload lifecycle.
 *
 * @see docs/forms/SEARCHABLE_INPUT_METADATA.md
 */
final class SearchableInputHelper
{
    /**
     * Hydrate a searchable input with a deterministic option payload.
     *
     * @param  Closure(int|string): array{value: int|string, label: string}|null  $optionResolver
     */
    public static function hydrate(SearchableInput $component, int|string|null $state, Closure $optionResolver): void
    {
        if ($state === null || $state === '') {
            self::resetComponent($component);

            return;
        }

        $option = $optionResolver($state);

        if ($option === null) {
            self::resetComponent($component);

            return;
        }

        $value = (string) $option['value'];
        $label = (string) $option['label'];

        $component
            ->state($value)
            ->options([$value => $label]);
    }

    /**
     * Clear dependent form keys once a searchable input loses its selection.
     *
     * @param  array<string, mixed>  $resets
     */
    public static function clear(callable $set, array $resets): void
    {
        foreach ($resets as $field => $value) {
            $set($field, $value);
        }
    }

    /**
     * Reset the UI component so Livewire forgets any stale selections.
     */
    private static function resetComponent(SearchableInput $component): void
    {
        $component
            ->state(null)
            ->options([]);
    }
}

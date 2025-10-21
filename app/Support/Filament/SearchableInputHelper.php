<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Illuminate\Contracts\Support\Arrayable;
use Stringable;

/**
 * Centralises Filament searchable input hydration so state mirrors the
 * documented payload lifecycle.
 *
 * @see docs/forms/SEARCHABLE_INPUT_METADATA.md
 */
final class SearchableInputHelper
{
    private function __construct()
    {
        // Static helper: prevent instantiation to highlight utility usage only.
    }

    /**
     * Hydrate a searchable input with a deterministic option payload.
     *
     * @param  Closure(int|string): (array{value: int|string, label: string|Stringable|Arrayable, payload?: array|Arrayable|Stringable}|null)  $optionResolver
     */
    public static function hydrate(SearchableInput $component, int|string|null $state, Closure $optionResolver): void
    {
        $normalised = self::normaliseState($state);

        if ($normalised === null) {
            self::resetComponent($component);

            return;
        }

        $option = $optionResolver($normalised);

        if (! is_array($option) || ! array_key_exists('value', $option) || ! array_key_exists('label', $option)) {
            self::resetComponent($component);

            return;
        }

        $value = (string) $option['value'];
        $label = (string) $option['label'];
        $payload = $option['payload'] ?? [];

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif (! is_array($payload)) {
            $payload = (array) $payload;
        }

        $component
            ->state($value)
            ->options([$value => $label]);

        if (method_exists($component, 'payload')) {
            $component->payload($payload);
        }
    }

    /**
     * Clear dependent form keys once a searchable input loses its selection.
     *
     * @param  array<string, mixed>  $resets
     */
    public static function clear(SearchableInput $component, callable $set, array $resets = []): void
    {
        self::resetComponent($component);

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

        if (method_exists($component, 'payload')) {
            $component->payload([]);
        }
    }

    private static function normaliseState(int|string|null $state): int|string|null
    {
        if ($state === null) {
            return null;
        }

        if (is_string($state)) {
            $trimmed = trim($state);

            if ($trimmed === '') {
                return null;
            }

            if (ctype_digit($trimmed)) {
                return (int) $trimmed;
            }

            return $trimmed;
        }

        return $state;
    }
}

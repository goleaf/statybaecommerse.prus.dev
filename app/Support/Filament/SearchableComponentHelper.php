<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Illuminate\Contracts\Support\Arrayable;
use Stringable;

/**
 * Centralises repetitive wiring around DefStudio's SearchableInput component.
 *
 * @phpstan-type NormalisedPayload array{
 *     value: string|int|null,
 *     label: string|Stringable|null,
 *     payload?: array<array-key, mixed>|Arrayable|null,
 * }
 */
final class SearchableComponentHelper
{
    private function __construct() {}

    /**
     * Hydrate a SearchableInput with consistent state, options, and payload assignments.
     *
     * @param Closure(mixed): (object|array|null)      $resolveRecord    Resolves the selected record from the persisted state.
     * @param Closure(object|array): NormalisedPayload $normalizePayload Normalises the resolved record into the component state +
     *                                                                   display payload tuple.
     */
    public static function hydrate(
        SearchableInput $component,
        mixed $state,
        Closure $resolveRecord,
        Closure $normalizePayload,
    ): void {
        // Early exit when no state is available so the component falls back to an empty input.
        if (self::stateIsEmpty($state)) {
            self::clear($component);

            return;
        }

        $record = $resolveRecord($state);

        // If the lookup fails we still want the UI to behave as if nothing was selected.
        if ($record === null) {
            self::clear($component);

            return;
        }

        /** @var NormalisedPayload $normalized */
        $normalized = $normalizePayload($record);

        $value = $normalized['value'] ?? $state;

        // Treat a missing or empty value as a signal to clear the component entirely.
        if (self::stateIsEmpty($value)) {
            self::clear($component);

            return;
        }

        $label = $normalized['label'] ?? '';

        if ($label instanceof Stringable) {
            $label = (string) $label;
        } elseif (! is_string($label)) {
            // Fallback to a simple cast so the dropdown always receives a string label.
            $label = (string) $label;
        }

        $payload = $normalized['payload'] ?? [];

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif (! is_array($payload)) {
            // Casting keeps loosely-typed payloads (for example, DTOs) compatible with Livewire serialisation.
            $payload = (array) $payload;
        }

        // Guarantee string state/options to match the SearchableInput expectation.
        $stringValue = (string) $value;

        $component
            ->state($stringValue)
            ->options([$stringValue => $label])
            ->payload($payload);
    }

    /**
     * Reset a SearchableInput to its pristine state while allowing callers to clear related form fields.
     */
    public static function clear(SearchableInput $component, Closure ...$clearRelated): void
    {
        // Wipe the component so Filament renders an empty dropdown and no metadata payload.
        $component
            ->state(null)
            ->options([])
            ->payload([]);

        // Execute any downstream clean-up callbacks so surrounding form state stays in sync.
        foreach ($clearRelated as $callback) {
            $callback();
        }
    }

    /**
     * Determine whether the provided state should be considered empty and therefore cleared.
     */
    private static function stateIsEmpty(mixed $state): bool
    {
        if ($state === null) {
            return true;
        }

        if (is_string($state) && trim($state) === '') {
            return true;
        }

        return false;
    }
}

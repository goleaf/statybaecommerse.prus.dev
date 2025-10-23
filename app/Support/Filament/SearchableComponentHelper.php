<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\DTO\SearchResult;
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
    /**
     * Hydrate a searchable component with its canonical SearchResult option.
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

        $result = $resolveResult($state);

        if (! $result instanceof SearchResult) {
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
     * Synchronise the underlying model key with the component state.
     *
     * The helper keeps the component options in sync with the canonical payload and wipes
     * everything when the state is empty so Livewire does not keep stale labels or metadata.
     *
     * @param  Closure(int|string):?SearchResult  $resolveResult  Resolves the current state into a SearchResult DTO.
     * @param  callable(string, mixed):void  $set  Filament's Set helper (or compatible callable) for updating state.
     */
    public static function sync(
        SearchableInput $component,
        ?string $state,
        callable $set,
        string $targetField,
        Closure $resolveResult
    ): void {
        if ($state === null || $state === '') {
            $set($targetField, null);
            self::clear($component);

            return;
        }

        $result = $resolveResult($state);

        if (! $result instanceof SearchResult) {
            $set($targetField, null);
            self::clear($component);

            return;
        }

        $value = $result->value();
        $set($targetField, is_numeric($value) ? (int) $value : $value);

        self::applyResult($component, $result);
    }

    /**
     * Reset the component back to an empty state.
     */
    public static function clear(SearchableInput $component): void
    {
        $component
            ->state(null)
            ->options([]);
    }

    private static function applyResult(SearchableInput $component, SearchResult $result): void
    {
        // Inject the canonical state and single-option list so Filament renders the stored label
        // and metadata payload exactly as the search services define it.
        $component
            ->state($result->value())
            ->options([$result]);
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

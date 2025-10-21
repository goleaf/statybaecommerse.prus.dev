<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

final class SearchableComponentHelper
{
    /**
     * Hydrate a searchable component with its canonical SearchResult option.
     *
     * @param  Closure(int|string):?SearchResult  $resolveResult  Resolves a persisted state into a SearchResult DTO.
     */
    public static function hydrate(SearchableInput $component, int|string|null $state, Closure $resolveResult): void
    {
        if (blank($state)) {
            self::clear($component);

            return;
        }

        $result = $resolveResult($state);

        if (! $result instanceof SearchResult) {
            self::clear($component);

            return;
        }

        self::applyResult($component, $result);
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
}

<?php

declare(strict_types=1);

namespace App\Support\Search;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Set;

/**
 * Centralises behaviour for hydrating and synchronising Filament SearchableInput components.
 */
final class SearchableComponentHelper
{
    /**
     * Hydrate a SearchableInput component from a stored identifier.
     *
     * @param  Closure(int|string): (\DefStudio\SearchableInput\DTO\SearchResult|array{id?: string, label?: string, payload?: array<string, mixed>})|null  $resolver
     */
    public static function hydrate(SearchableInput $component, int|string|null $state, Closure $resolver): void
    {
        if ($state === null || $state === '') {
            self::clear($component);

            return;
        }

        $result = $resolver($state);

        if ($result === null) {
            self::clear($component);

            return;
        }

        $normalised = SearchResultPayload::hydrate($result);

        // Persist the selection so the UI displays the correct label after hydration.
        $component
            ->state((string) $normalised['id'])
            ->options([
                (string) $normalised['id'] => $normalised['label'],
            ]);
    }

    /**
     * Update a related attribute when a SearchableInput state changes.
     *
     * @param  Closure(string): (\DefStudio\SearchableInput\DTO\SearchResult|array{id?: string, label?: string, payload?: array<string, mixed>})|null  $resolver
     */
    public static function syncSelectedRecord(
        SearchableInput $component,
        ?string $state,
        Set $set,
        string $attribute,
        Closure $resolver,
    ): void {
        if ($state === null || $state === '') {
            // Clearing the selection must remove both the stored identifier and any UI remnants.
            $set($attribute, null);
            self::clear($component);

            return;
        }

        $result = $resolver($state);

        if ($result === null) {
            $set($attribute, null);
            self::clear($component);

            return;
        }

        $normalised = SearchResultPayload::hydrate($result);
        $identifier = $normalised['id'];

        // Persist the related key using an integer when appropriate.
        $set($attribute, is_numeric($identifier) ? (int) $identifier : $identifier);

        // Refresh the UI with the normalised label to avoid stale selections.
        $component
            ->state((string) $identifier)
            ->options([
                (string) $identifier => $normalised['label'],
            ]);
    }

    /**
     * Reset the component state to avoid leaking stale payloads in Livewire form data.
     */
    public static function clear(SearchableInput $component): void
    {
        $component
            ->state(null)
            ->options([]);
    }
}

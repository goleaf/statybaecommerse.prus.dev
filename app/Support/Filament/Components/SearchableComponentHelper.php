<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

/**
 * Centralises SearchableInput state helpers so resources can stay lean and readable.
 */
final class SearchableComponentHelper
{
    private function __construct()
    {
        // Intentionally left empty because this helper exposes only static utility methods.
    }

    /**
     * Normalise the identifier into an integer or null when the component is empty.
     */
    public static function normaliseIdentifier(int|string|null $state): ?int
    {
        if ($state === null) {
            return null;
        }

        if (is_string($state)) {
            $state = trim($state);

            if ($state === '') {
                return null;
            }
        }

        return (int) $state;
    }

    /**
     * Reset a SearchableInput component to its pristine state (no state and no options).
     */
    public static function clearComponent(SearchableInput $component): void
    {
        $component->state(null);
        $component->options([]);
    }

    /**
     * Hydrate a SearchableInput component using a resolved Eloquent model instance.
     *
     * @template TModel of Model
     *
     * @param TModel|null $model
     * @param callable(TModel): string $labelResolver Resolve the label for the hydrated option.
     */
    public static function hydrateFromModel(
        SearchableInput $component,
        ?int $state,
        ?Model $model,
        callable $labelResolver
    ): void {
        if ($state === null || ! $model instanceof Model) {
            return;
        }

        $component
            ->state((string) $state)
            ->options([
                (string) $model->getKey() => $labelResolver($model),
            ]);
    }

    /**
     * Hydrate a SearchableInput component by resolving the model through a finder callback.
     *
     * @template TModel of Model
     *
     * @param callable(int): (TModel|null) $finder Retrieve the model from a persisted store.
     * @param callable(TModel): string $labelResolver Resolve the label for the hydrated option.
     */
    public static function hydrateUsingFinder(
        SearchableInput $component,
        ?int $state,
        callable $finder,
        callable $labelResolver
    ): void {
        if ($state === null) {
            return;
        }

        $model = $finder($state);

        if (! $model instanceof Model) {
            return;
        }

        self::hydrateFromModel($component, $state, $model, $labelResolver);
    }

    /**
     * Persist a nullable relation identifier by normalising the raw component state first.
     */
    public static function syncNullableIntState(int|string|null $state, Set $set, string $field): void
    {
        $set($field, self::normaliseIdentifier($state));
    }

    /**
     * Synchronise a lookup component with its downstream payload consumer.
     *
     * @template TModel of Model
     *
     * @param int|string|null $state Raw component state that may be null, empty, or a string identifier.
     * @param callable(int): (TModel|null) $finder Resolve the model backing the lookup.
     * @param callable(TModel): array<string, mixed> $payloadResolver Build the normalized payload for dependent components.
     * @param callable(TModel): string|null $labelResolver Optionally resolve an explicit label for the lookup component.
     * @param array<string, mixed> $emptyPayload Provide the default payload when no selection exists.
     */
    public static function syncLookupPayload(
        SearchableInput $component,
        int|string|null $state,
        Set $set,
        string $payloadField,
        callable $finder,
        callable $payloadResolver,
        ?callable $labelResolver = null,
        array $emptyPayload = []
    ): void {
        $identifier = self::normaliseIdentifier($state);

        if ($identifier === null) {
            self::clearComponent($component);
            $set($payloadField, $emptyPayload);

            return;
        }

        $model = $finder($identifier);

        if (! $model instanceof Model) {
            self::clearComponent($component);
            $set($payloadField, $emptyPayload);

            return;
        }

        $component->state((string) $identifier);

        if ($labelResolver !== null) {
            $label = $labelResolver($model);

            if (is_string($label) && $label !== '') {
                $component->options([
                    (string) $identifier => $label,
                ]);
            }
        }

        $set($payloadField, $payloadResolver($model));
    }
}

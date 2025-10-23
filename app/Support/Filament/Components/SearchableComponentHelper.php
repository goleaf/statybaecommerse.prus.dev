<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

/**
 * Helper utilities to normalize SearchableInput hydration and clearing logic.
 */
final class SearchableComponentHelper
{
    /**
     * Guarded constructor to prevent instantiation.
     */
    private function __construct()
    {
        // Helper only exposes static APIs.
    }

    /**
     * Hydrate a SearchableInput component using an already loaded model instance.
     *
     * @template TModel of Model
     *
     * @param TModel|null              $model
     * @param callable(TModel): string $labelResolver Resolve the label for the hydrated option.
     */
    public static function hydrateFromRecord(
        SearchableInput $component,
        ?int $state,
        ?Model $record,
        Closure $labelResolver,
    ): void {
        if ($state === null || $record === null) {
            return;
        }

        $component
            ->state((string) $record->getKey())
            ->options([
                (string) $record->getKey() => $labelResolver($record),
            ]);
    }

    /**
     * Hydrate a SearchableInput component by resolving a model lazily.
     *
     * @template TModel of Model
     *
     * @param callable(int): (TModel|null) $finder        Retrieve the model from a persisted store.
     * @param callable(TModel): string     $labelResolver Resolve the label for the hydrated option.
     */
    public static function hydrateUsingResolver(
        SearchableInput $component,
        ?int $state,
        Closure $resolver,
        Closure $labelResolver,
    ): void {
        if ($state === null) {
            return;
        }

        $record = $resolver($state);

        if (! $record instanceof Model) {
            return;
        }

        self::hydrateFromRecord($component, $state, $record, $labelResolver);
    }

    /**
     * Persist a nullable relation identifier by normalising the raw component state first.
     *
     * When the resolved identifier is null, the associated SearchableInput component is
     * also cleared to keep its options and label in sync with the stored value. Refer to
     * docs/forms/SEARCHABLE_INPUT_HELPER.md for behavioural notes around this helper.
     */
    public static function syncNullableIntState(
        int|string|null $state,
        Set $set,
        string $field,
        ?SearchableInput $component = null
    ): void {
        $identifier = self::normaliseIdentifier($state);

        if ($identifier === null) {
            $set($field, null);

            if ($component !== null) {
                self::clearComponent($component);
            }

            return;
        }

        $set($field, $identifier);
    }

    /**
     * Synchronize a lookup component with an associated payload array (e.g. address metadata).
     *
     * @template TModel of Model
     *
     * @param int|string|null                        $state           Raw component state that may be null, empty, or a string identifier.
     * @param callable(int): (TModel|null)           $finder          Resolve the model backing the lookup.
     * @param callable(TModel): array<string, mixed> $payloadResolver Build the normalized payload for dependent components.
     * @param callable(TModel): string|null          $labelResolver   Optionally resolve an explicit label for the lookup component.
     * @param array<string, mixed>                   $emptyPayload    Provide the default payload when no selection exists.
     */
    public static function syncLookupPayload(
        Set $set,
        string $lookupField,
        string $payloadField,
        ?string $state,
        Closure $payloadResolver,
    ): void {
        if ($state === null || $state === '') {
            $set($lookupField, null);
            $set($payloadField, []);

            return;
        }

        $payload = $payloadResolver((int) $state);

        if ($payload === null) {
            return;
        }

        $set($payloadField, $payload);
    }
}

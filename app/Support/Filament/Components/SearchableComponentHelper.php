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
     * @param Closure(Model): string $labelResolver
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
     * @param Closure(int): (Model|null) $resolver
     * @param Closure(Model): string     $labelResolver
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
     * Assign a nullable integer identifier based on the raw SearchableInput state.
     */
    public static function assignNullableId(Set $set, string $property, ?string $state): void
    {
        if ($state === null || $state === '') {
            $set($property, null);

            return;
        }

        $set($property, (int) $state);
    }

    /**
     * Synchronize a lookup component with an associated payload array (e.g. address metadata).
     *
     * @param Closure(int): (?array) $payloadResolver
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

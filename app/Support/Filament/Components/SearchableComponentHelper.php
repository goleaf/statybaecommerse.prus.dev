<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Set;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Stringable;

/**
 * Centralises SearchableInput state helpers so resources can stay lean and readable.
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
     * Guarded constructor to prevent instantiation.
     */
    private function __construct()
    {
        // Helper only exposes static APIs.
    }

    /**
     * Hydrate a SearchableInput with consistent state, options, and payload assignments.
     *
     * @param Closure(mixed): (object|array|null)      $resolveRecord    Resolves the selected record from the persisted state.
     * @param Closure(object|array): NormalisedPayload $normalizePayload Normalises the resolved record into the component state
     *                                                                   and payload tuple.
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

        $normalised = self::normaliseResolvedRecord($record, $state, $normalizePayload);

        if ($normalised === null) {
            self::clear($component);

            return;
        }

        self::applyComponentState($component, $normalised);
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
     * Synchronise related state when a SearchableInput selection changes.
     *
     * @param Closure(string|int): (object|array|null)                                                    $resolveRecord    Locate the record backing the provided state.
     * @param Closure(object|array): NormalisedPayload                                                    $normalizePayload Convert the record into the helper payload tuple.
     * @param (Closure(array{value:int|string,label:string,payload:array<string|int, mixed>}): void)|null $onSync           Optional callback invoked when the record successfully resolves.
     * @param (Closure(): void)|null                                                                      $onClear          Optional callback invoked whenever the component is cleared.
     */
    public static function syncSelectedRecord(
        SearchableInput $component,
        ?string $state,
        Set $set,
        string $attribute,
        Closure $resolveRecord,
        Closure $normalizePayload,
        ?Closure $onSync = null,
        ?Closure $onClear = null,
    ): void {
        // Treat empty strings and null values as clear actions.
        if (self::stateIsEmpty($state)) {
            $set($attribute, null);
            self::clear($component, ...self::wrapOptionalCallback($onClear));

            return;
        }

        $record = $resolveRecord($state);

        if ($record === null) {
            $set($attribute, null);
            self::clear($component, ...self::wrapOptionalCallback($onClear));

            return;
        }

        $normalised = self::normaliseResolvedRecord($record, $state, $normalizePayload);

        if ($normalised === null) {
            $set($attribute, null);
            self::clear($component, ...self::wrapOptionalCallback($onClear));

            return;
        }

        self::applyComponentState($component, $normalised);

        $identifier = $normalised['value'];
        $set($attribute, is_numeric($identifier) ? (int) $identifier : $identifier);

        if ($onSync !== null) {
            // Surface the fully normalised payload so callers can hydrate dependent form data.
            $onSync($normalised);
        }
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
     * Reset a SearchableInput component to its pristine state (alias for {@see clear()}).
     */
    public static function clearComponent(SearchableInput $component): void
    {
        self::clear($component);
    }

    /**
     * Hydrate a SearchableInput component using a resolved Eloquent model instance.
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

        // Preserve parity with the richer helper by pushing an empty payload alongside state/options.
        self::applyComponentState($component, [
            'value'   => $record->getKey(),
            'label'   => $labelResolver($record),
            'payload' => [],
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
     * also cleared to keep its options, label, and payload in sync with the stored value.
     * Refer to docs/forms/SEARCHABLE_INPUT_HELPER.md for behavioural notes around this helper.
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
                self::clear($component);
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
        array $emptyPayload = []
    ): void {
        $identifier = self::normaliseIdentifier($state);

        if ($identifier === null) {
            $set($lookupField, null);
            $set($payloadField, $emptyPayload);

            return;
        }

        $set($lookupField, $identifier);
        $payload = $payloadResolver($identifier);

        if ($payload === null) {
            $set($payloadField, $emptyPayload);

            return;
        }

        $set($payloadField, is_array($payload) ? $payload : (array) $payload);
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

    /**
     * Convert the resolved record into the tuple consumed by SearchableInput state helpers.
     *
     * @return array{value:int|string, label:string, payload: array<array-key, mixed>}|null
     */
    private static function normaliseResolvedRecord(
        object|array $record,
        mixed $state,
        Closure $normalizePayload,
    ): ?array {
        /** @var NormalisedPayload $normalised */
        $normalised = $normalizePayload($record);

        $value = $normalised['value'] ?? $state;

        // Bail out when the normaliser cannot determine a usable identifier.
        if (self::stateIsEmpty($value)) {
            return null;
        }

        $label = $normalised['label'] ?? '';

        if ($label instanceof Stringable) {
            $label = (string) $label;
        } elseif (! is_string($label)) {
            // Fallback to a simple cast so the dropdown always receives a string label.
            $label = (string) $label;
        }

        $payload = $normalised['payload'] ?? [];

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif (! is_array($payload)) {
            // Casting keeps loosely-typed payloads (for example, DTOs) compatible with Livewire serialisation.
            $payload = (array) $payload;
        }

        return [
            'value'   => $value,
            'label'   => $label,
            'payload' => $payload,
        ];
    }

    /**
     * Apply the normalised value, label, and payload to the SearchableInput component.
     *
     * @param array{value:int|string, label:string, payload: array<array-key, mixed>} $normalised
     */
    private static function applyComponentState(SearchableInput $component, array $normalised): void
    {
        $stringValue = (string) $normalised['value'];

        $component
            ->state($stringValue)
            ->options([$stringValue => $normalised['label']])
            ->payload($normalised['payload']);
    }

    /**
     * Wrap an optional callback in an array so it can be unpacked into variadic parameters.
     *
     * @return array<int, Closure>
     */
    private static function wrapOptionalCallback(?Closure $callback): array
    {
        return $callback !== null ? [$callback] : [];
    }
}

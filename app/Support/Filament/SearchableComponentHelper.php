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
    public const PAYLOAD_META_KEY = 'searchable_payload';

    private const FALLBACK_META_KEY = 'searchable_payload_fallback';

    private static bool $payloadMacrosRegistered = false;

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
        self::ensurePayloadMacros();

        // Early exit when no state is available so the component falls back to an empty input.
        if (self::stateIsEmpty($state)) {
            self::clear($component);

            return;
        }

        $normalised = self::resolveNormalisedTuple($state, $resolveRecord, $normalizePayload);

        if ($normalised === null) {
            self::clear($component);

            return;
        }

        // Guarantee string state/options to match the SearchableInput expectation and persist the canonical payload.
        $component
            ->state($normalised['value'])
            ->options([$normalised['value'] => $normalised['label']])
            ->payload($normalised['payload']);
    }

    /**
     * Sync a SearchableInput selection with a related attribute and optional payload snapshot.
     *
     * @param callable(string, mixed): mixed           $set              Filament Set helper (or compatible callable) for persisting state.
     * @param Closure(string|int): (object|array|null) $resolveRecord    Resolves the selected record for payload extraction.
     * @param Closure(object|array): NormalisedPayload $normalizePayload Normalises the record into the helper tuple.
     * @param array<array-key, mixed>|Arrayable|null   $emptyPayload     Default payload when the lookup is cleared.
     * @param Closure                                  ...$clearRelated  Optional callbacks executed after the component clears.
     */
    public static function syncSelectedRecord(
        SearchableInput $component,
        ?string $state,
        callable $set,
        string $attribute,
        Closure $resolveRecord,
        Closure $normalizePayload,
        ?string $payloadField = null,
        array|Arrayable|null $emptyPayload = null,
        Closure ...$clearRelated,
    ): void {
        self::ensurePayloadMacros();

        $emptyPayload = self::normaliseEmptyPayload($emptyPayload);

        self::ensurePayloadMacros();

        $clearSelection = static function () use ($component, $set, $attribute, $payloadField, $emptyPayload, $clearRelated): void {
            // Reset the persisted identifier alongside the lookup metadata to avoid stale state.
            $set($attribute, null);

            if ($payloadField !== null) {
                $set($payloadField, $emptyPayload);
            }

            self::clear($component, ...$clearRelated);
        };

        // Clearing the lookup should wipe downstream state immediately.
        if (self::stateIsEmpty($state)) {
            $clearSelection();

            return;
        }

        $normalised = self::resolveNormalisedTuple($state, $resolveRecord, $normalizePayload);

        if ($normalised === null) {
            $clearSelection();

            return;
        }

        $identifier = $normalised['value'];
        $persistedIdentifier = is_numeric($identifier) ? (int) $identifier : $identifier;

        // Persist the identifier using a sensible scalar type so database columns stay aligned.
        $set($attribute, $persistedIdentifier);

        if ($payloadField !== null) {
            // Store the canonical payload for downstream automation without dehydrating it.
            $set($payloadField, $normalised['payload']);
        }

        $component
            ->state($identifier)
            ->options([$identifier => $normalised['label']])
            ->payload($normalised['payload']);
    }

    /**
     * Reset a SearchableInput to its pristine state while allowing callers to clear related form fields.
     */
    public static function clear(SearchableInput $component, Closure ...$clearRelated): void
    {
        self::ensurePayloadMacros();

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

    /**
     * Resolve and normalise the helper tuple for component consumption.
     *
     * @return array{value: string, label: string, payload: array<string, mixed>}|null
     */
    private static function resolveNormalisedTuple(
        mixed $state,
        Closure $resolveRecord,
        Closure $normalizePayload,
    ): ?array {
        $record = $resolveRecord($state);

        // Bail out when the lookup cannot be resolved so the component clears gracefully.
        if ($record === null) {
            return null;
        }

        /** @var NormalisedPayload $normalised */
        $normalised = $normalizePayload($record);

        $value = $normalised['value'] ?? $state;

        if (self::stateIsEmpty($value)) {
            return null;
        }

        $label = self::normaliseLabel($normalised['label'] ?? '');

        $stringValue = (string) $value;
        $payload = self::normalisePayload($normalised['payload'] ?? [], $stringValue, $label);

        return [
            'value'   => $stringValue,
            'label'   => $label,
            'payload' => $payload,
        ];
    }

    /**
     * Ensure labels are always cast to strings for Livewire serialisation.
     */
    private static function normaliseLabel(mixed $label): string
    {
        if ($label instanceof Stringable) {
            return (string) $label;
        }

        if (! is_string($label)) {
            return is_scalar($label) ? (string) $label : '';
        }

        return $label;
    }

    /**
     * Normalise payload arrays so every consumer receives the canonical `{id, label, ...}` structure.
     *
     * @param  array<array-key, mixed>|Arrayable|null $payload
     * @return array<string, mixed>
     */
    private static function normalisePayload(array|Arrayable|null $payload, string $value, string $label): array
    {
        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif (! is_array($payload)) {
            // Casting keeps loosely-typed payloads (for example, DTOs) compatible with Livewire serialisation.
            $payload = (array) $payload;
        }

        if (! array_key_exists('id', $payload)) {
            $payload['id'] = $value;
        } else {
            $payload['id'] = (string) $payload['id'];
        }

        if (! array_key_exists('label', $payload)) {
            $payload['label'] = $label;
        } else {
            $payload['label'] = self::normaliseLabel($payload['label']);
        }

        return $payload;
    }

    /**
     * Provide a predictable empty payload when the lookup resets.
     *
     * @param  array<array-key, mixed>|Arrayable|null $payload
     * @return array<string, mixed>
     */
    private static function normaliseEmptyPayload(array|Arrayable|null $payload): array
    {
        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif ($payload === null) {
            $payload = [];
        } elseif (! is_array($payload)) {
            $payload = (array) $payload;
        }

        if (! array_key_exists('id', $payload)) {
            $payload['id'] = null;
        }

        if (! array_key_exists('label', $payload)) {
            $payload['label'] = '';
        } else {
            $payload['label'] = self::normaliseLabel($payload['label']);
        }

        return $payload;
    }

    /**
     * Register payload helper macros the first time the helper is exercised so downstream calls stay type-safe.
     */
    public static function registerPayloadMacros(): void
    {
        self::ensurePayloadMacros();
    }

    /**
     * Lazily attach payload macros to the SearchableInput component.
     */
    private static function ensurePayloadMacros(): void
    {
        if (self::$payloadMacrosRegistered) {
            if (SearchableInput::hasMacro('payload') && SearchableInput::hasMacro('getPayload')) {
                return;
            }

            // When tests flush macros mid-run we need to re-register them, so mark the flag for another pass.
            self::$payloadMacrosRegistered = false;
        }

        if (! class_exists(SearchableInput::class)) {
            return;
        }

        if (! SearchableInput::hasMacro('payload')) {
            SearchableInput::macro('payload', function (array $payload): SearchableInput {
                /** @var SearchableInput $this */
                return $this->meta(SearchableComponentHelper::PAYLOAD_META_KEY, $payload);
            });
        }

        if (! SearchableInput::hasMacro('fallbackPayload')) {
            SearchableInput::macro('fallbackPayload', function (?array $payload = null): SearchableInput {
                /** @var SearchableInput $this */
                return $this->meta(
                    SearchableComponentHelper::FALLBACK_META_KEY,
                    $payload ?? [],
                );
            });
        }

        if (! SearchableInput::hasMacro('getPayload')) {
            SearchableInput::macro('getPayload', function (): array {
                /** @var SearchableInput $this */
                $meta = method_exists($this, 'getMeta') ? $this->getMeta() : [];
                $meta = is_array($meta) ? $meta : [];

                if (array_key_exists(SearchableComponentHelper::PAYLOAD_META_KEY, $meta)) {
                    return (array) $meta[SearchableComponentHelper::PAYLOAD_META_KEY];
                }

                if (array_key_exists(SearchableComponentHelper::FALLBACK_META_KEY, $meta)) {
                    return (array) $meta[SearchableComponentHelper::FALLBACK_META_KEY];
                }

                return [];
            });
        }

        self::$payloadMacrosRegistered = true;
    }
}

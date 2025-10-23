<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Support\Search\SearchResultPayload;
use Closure;
use DefStudio\SearchableInput\DTO\SearchResult;
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

        $option = self::normaliseOption($optionResolver($normalised));

        if ($option === null) {
            self::resetComponent($component);

            return;
        }

        $value = (string) $option['value'];
        $label = (string) $option['label'];
        $payload = $option['payload'];

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

    /**
     * Convert various resolver return types into the canonical option payload.
     *
     * The helper accepts arrays, {@see Arrayable} structures, and the native
     * {@see SearchResult} DTO used by the DefStudio package. Anything else is
     * treated as an invalid option and coerced to `null` so the component is
     * cleared upstream.
     *
     * @param  array{value?: int|string, id?: int|string, label?: string|Stringable|Arrayable, payload?: mixed, data?: array<mixed>}|Arrayable|SearchResult|null  $option
     * @return array{value: int|string, label: string, payload: array<string, mixed>}|null
     */
    private static function normaliseOption(Arrayable|SearchResult|array|null $option): ?array
    {
        if ($option instanceof SearchResult) {
            $hydrated = SearchResultPayload::hydrate($option);

            return [
                'value'   => $hydrated['id'],
                'label'   => $hydrated['label'],
                'payload' => $hydrated['payload'],
            ];
        }

        if ($option instanceof Arrayable) {
            $option = $option->toArray();
        }

        if (! is_array($option)) {
            return null;
        }

        $value = $option['value'] ?? $option['id'] ?? null;
        $label = $option['label'] ?? null;

        if ($value === null || $label === null) {
            return null;
        }

        if ($label instanceof Arrayable) {
            $label = $label->toArray();
        }

        if ($label instanceof Stringable) {
            $label = (string) $label;
        }

        if (is_array($label)) {
            $label = implode(' ', array_map(static fn ($part): string => (string) $part, $label));
        }

        $payload = $option['payload'] ?? $option['data'] ?? [];

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif (! is_array($payload)) {
            $payload = (array) $payload;
        }

        return [
            'value'   => $value,
            'label'   => (string) $label,
            'payload' => $payload,
        ];
    }
}

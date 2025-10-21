<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

/**
 * Centralises repetitive wiring around DefStudio's SearchableInput component.
 */
final class SearchableComponentHelper
{
    private function __construct() {}

    /**
     * Hydrate a SearchableInput with consistent state, options, and payload assignments.
     *
     * @param Closure(mixed): (object|array|null) $resolveRecord Resolves the selected record from the persisted state.
     * @param Closure(object|array): array{value: string|int|null, label: string, payload?: array} $normalizePayload Normalises
     *        the resolved record into the component state + display payload tuple.
     */
    public static function hydrate(
        SearchableInput $component,
        mixed $state,
        Closure $resolveRecord,
        Closure $normalizePayload,
    ): void {
        // Early exit when no state is available so the component falls back to an empty input.
        if ($state === null || $state === '') {
            self::clear($component);

            return;
        }

        $record = $resolveRecord($state);

        // If the lookup fails we still want the UI to behave as if nothing was selected.
        if ($record === null) {
            self::clear($component);

            return;
        }

        $normalized = $normalizePayload($record);
        $value = $normalized['value'] ?? $state;
        $label = $normalized['label'] ?? '';
        $payload = $normalized['payload'] ?? [];

        // Guarantee string state/options to match the SearchableInput expectation.
        $stringValue = $value === null ? null : (string) $value;

        $component
            ->state($stringValue)
            ->options($stringValue === null ? [] : [$stringValue => $label])
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
}

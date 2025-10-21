<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

final class SearchableComponentHelper
{
    /**
     * Hydrate a SearchableInput with deterministic state, options, and payload metadata.
     *
     * @template TRecord of object|array
     *
     * @param int|string|null                                                                           $state             Current raw component state from the form.
     * @param Closure(int|string|null): (?TRecord)                                                      $recordResolver    Callback that resolves the backing record or null when missing.
     * @param Closure(TRecord): array{value: int|string, label: string, payload?: array<string, mixed>} $payloadNormaliser Shapes the UI payload.
     */
    public static function hydrate(
        SearchableInput $component,
        int|string|null $state,
        Closure $recordResolver,
        Closure $payloadNormaliser,
    ): void {
        // Abort hydration when no identifier is present so stale UI state does not linger.
        if ($state === null || $state === '') {
            self::clear($component);

            return;
        }

        $record = $recordResolver($state);

        // Clear the component whenever the lookup misses to avoid exposing mismatched labels.
        if ($record === null) {
            self::clear($component);

            return;
        }

        $normalised = $payloadNormaliser($record);

        $value = (string) ($normalised['value'] ?? $state);
        $label = (string) ($normalised['label'] ?? $value);

        /** @var array<string, mixed> $payload */
        $payload = $normalised['payload'] ?? [];

        // Guarantee the payload always surfaces the identifier and label for downstream consumers.
        $payload['id'] ??= $value;
        $payload['label'] ??= $label;

        // Apply the canonical state, option label, and metadata payload to the component in one place.
        $component
            ->state($value)
            ->options([
                $value => $label,
            ])
            ->meta('payload', $payload);
    }

    /**
     * Reset the component and optionally let callers clear related form fields via callbacks.
     */
    public static function clear(SearchableInput $component, Closure ...$resetCallbacks): void
    {
        // Drop any previously selected value, label, or payload so dependent inputs do not see stale state.
        $component
            ->state(null)
            ->options([])
            ->meta('payload', []);

        // Allow callers to reset additional form state (e.g. dependent fields, payload mirrors, etc.).
        foreach ($resetCallbacks as $callback) {
            $callback();
        }
    }
}

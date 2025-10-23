# SearchableInput Helper Reference

The `App\\Support\\Filament\\Components\\SearchableComponentHelper` centralizes the repetitive wiring that our Filament `SearchableInput` fields need when hydrating existing records or clearing lookups.

## Available Utilities

| Scenario | Helper | Notes |
| --- | --- | --- |
| Hydrating an edit form with an existing model | `hydrateFromModel()` or `hydrateUsingFinder()` | Restores the component state and option list from a model instance without duplicating query logic. |
| Persisting nullable foreign keys | `syncNullableIntState()` | Normalises blank values to `null`, clears the component when empty, and persists integer IDs. |
| Propagating lookup payloads to dependent fields | `syncLookupPayload()` | Keeps downstream payload arrays in sync and clears the lookup component when state is removed. |

## Usage Notes

1. Normalise raw component values with `normaliseIdentifier()` before casting to integers.
2. Clear the lookup via `clearComponent()` whenever the helper determines the state is empty.
3. Pass the optional component instance to `syncNullableIntState()` so clearing logic stays centralised.
4. Use the optional label resolver in `syncLookupPayload()` when a component needs its option list rebuilt (e.g. editing existing orders).

> **Tip:** The helper intentionally returns payload arrays untouched, so dependent totals, key-value components, or computed summaries continue to consume the normalised data emitted by search payload builders like `AddressSearch::payload()`.

## Example

```php
SearchableInput::make('customer_id')
    ->searchUsing(fn (string $search) => CustomerSearch::byEmailPhoneName($search))
    ->dehydrateStateUsing(static fn ($state) => SearchableComponentHelper::normaliseIdentifier($state))
    ->afterStateHydrated(fn (SearchableInput $component, ?int $state) =>
        SearchableComponentHelper::hydrateUsingFinder($component, $state, $finder, $labelResolver)
    )
    ->afterStateUpdated(fn (SearchableInput $component, $state, Set $set) =>
        SearchableComponentHelper::syncNullableIntState($state, $set, 'customer_id', $component)
    );

```

## Payload expectations

- `syncLookupPayload()` expects the payload resolver to return associative arrays ready for downstream consumers (e.g. `KeyValue` components or computed totals). The helper writes these arrays verbatim so structure the payload according to the receiving field.
- Provide an `$emptyPayload` value mirroring the target field's default shape. For example, supply an empty array for key-value components or a zeroed structure for totals so the UI remains consistent when the lookup is cleared.
- When using the optional label resolver, return the same text you would normally push into `$component->options()` to keep edit forms hydrated correctly.

# Searchable Input Helper

The `App\Support\Filament\Components\SearchableComponentHelper` centralises common state management tasks for [`DefStudio\SearchableInput`](https://github.com/defstudio/searchable-input) components so Filament resources stay concise.

## Core scenarios

| Scenario | Helper | Notes |
| --- | --- | --- |
| Hydrating an edit form with an existing model | `hydrateFromModel()` or `hydrateUsingFinder()` | Restores the component state and option list from a model instance without duplicating query logic. |
| Persisting nullable foreign keys | `syncNullableIntState()` | Normalises blank values to `null` before saving integer IDs. |
| Propagating lookup payloads to dependent fields | `syncLookupPayload()` | Keeps downstream payload arrays in sync and clears the lookup component when state is removed. |

## Usage checklist

1. Normalise raw component values with `normaliseIdentifier()` before casting to integers.
2. Clear the lookup via `clearComponent()` whenever the helper determines the state is empty.
3. Use the optional label resolver in `syncLookupPayload()` when a component needs its option list rebuilt (e.g. editing existing orders).

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
        SearchableComponentHelper::syncNullableIntState($state, $set, 'customer_id')
    );
```

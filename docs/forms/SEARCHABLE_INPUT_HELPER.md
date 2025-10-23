# SearchableInput Helper Reference

The `App\Support\Filament\Components\SearchableComponentHelper` centralises the repetitive work required to keep [`DefStudio\SearchableInput`](https://github.com/defstudio/searchable-input) fields hydrated with the correct state, option list, and metadata payload. By funnelling hydration and update hooks through the helper, resources inherit consistent behaviour while reusing the canonical `{ value, label, payload }` tuple produced by our search services.

## Available Utilities

| Scenario | Helper | Notes |
| --- | --- | --- |
| Hydrating an edit form with an existing model | `hydrate()` | Resolves the stored identifier, normalises the payload, and feeds the dropdown state/options/payload tuple back into the component. |
| Updating related attributes after a selection changes | `syncSelectedRecord()` | Persists the identifier to another form field (for example, `product_id`) while rebuilding the dropdown state and payload metadata. Optional callbacks let resources cache enriched payloads or clear dependant fields. |
| Clearing stale selections | `clear()` | Resets the component state, options, and payload while executing any supplied callbacks to tidy related form data. |

## Usage Notes

1. Provide a resolver that accepts the persisted identifier and returns the backing record (`hydrate()` and `syncSelectedRecord()` receive the raw component state).
2. The normaliser should emit the `{ value, label, payload }` tuple—`payload` can be any associative array or `Arrayable` instance.
3. Use the optional `$onSync` callback on `syncSelectedRecord()` to mirror payload metadata into hidden fields or computed totals.
4. Supply the optional `$onClear` callback to wipe dependant form fields whenever the lookup is emptied or fails to resolve.

> **Tip:** When your search service already emits a `SearchResult` via `SearchResultPayload::normalise()`, call `SearchResultPayload::hydrate()` inside the normaliser and return the resulting tuple to avoid duplicating field mapping logic.

## Example

```php
SearchableInput::make('product_id')
    ->searchUsing(fn (string $search) => ProductSearch::complex($search))
    ->afterStateHydrated(fn (SearchableInput $component, ?int $state) => SearchableComponentHelper::hydrate(
        component: $component,
        state: $state,
        resolveRecord: fn (int $id) => Product::query()->select(['id', 'sku', 'name', 'price'])->find($id),
        normalizePayload: fn (Product $product) => [
            'value'   => $product->getKey(),
            'label'   => ProductSearch::label($product),
            'payload' => [
                'product_id' => $product->getKey(),
                'sku'        => (string) $product->sku,
                'name'       => $product->getTranslatedName(),
                'price'      => (float) ($product->price ?? 0),
            ],
        ],
    ))
    ->afterStateUpdated(fn (SearchableInput $component, ?string $state, Set $set) => SearchableComponentHelper::syncSelectedRecord(
        component: $component,
        state: $state,
        set: $set,
        attribute: 'product_id',
        resolveRecord: fn (string $id) => Product::find((int) $id),
        normalizePayload: fn (Product $product) => [...],
        onSync: fn (array $normalised) => $set('product_payload', $normalised['payload']),
        onClear: fn () => $set('product_payload', []),
    ));

Hidden::make('product_payload')
    ->default([])
    ->dehydrated(false);
```

## Payload expectations

- The helper casts labels to strings and payloads to arrays, ensuring Livewire receives serialisable data even when DTOs or value objects back the metadata.
- When the normaliser returns an empty identifier, the helper automatically delegates to `clear()` so the UI cannot surface stale payloads.
- Downstream components should consume the payload array as the single source of truth—search services embed IDs, labels, and domain-specific metadata (SKU, price, etc.) by default.

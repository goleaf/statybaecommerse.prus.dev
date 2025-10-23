# Searchable input hydration helper

`App\\Support\\Filament\\SearchableComponentHelper` centralises how Filament search widgets restore their state after Livewire
refreshes. Call the helper inside `afterStateHydrated()` or `afterStateUpdated()` hooks so every searchable input reuses the
same conventions.

## Hydrating from existing records

```php
use App\Support\Filament\SearchableComponentHelper;
use App\Support\Search\ProductSearch;
use App\Models\Product;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

SearchableInput::make('product_id')
    ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
        SearchableComponentHelper::hydrate(
            component: $component,
            state: $state,
            recordResolver: static fn (?int $id): ?Product => $id === null
                ? null
                : Product::query()->select(['id', 'sku', 'name'])->find($id),
            payloadNormaliser: static fn (Product $product): array => [
                'value' => $product->getKey(),
                'label' => ProductSearch::label($product),
                // Keep the payload shape predictable for JavaScript consumers.
                'payload' => ProductSearch::payload($product),
            ],
        );
    });
```

The normaliser closure **must** return the selected value, display label, and an array payload. The helper automatically adds the
`id` and `label` keys to the payload so down-stream consumers always receive both fields.

The helper converts the `value` to a string, registers it as the component state, and feeds the label through `options()` alongside the payload so downstream closures all read the same structure. When the normaliser returns an `Arrayable` payload (for example, a DTO implementing `toArray()`), the helper coerces it into an array before handing it over to Livewire. Empty or falsy identifiers short-circuit into `clear()` so the UI cannot surface stale metadata.

Re-use the specialised `App\Support\Search\*Search` helpers whenever possible. They already return `SearchResult` DTOs with a
consistent payload structure for JavaScript and PHP consumers. When you cannot rely on an existing helper, mirror the following
pattern:

```php
payloadNormaliser: static fn (YourModel $record): array => [
    'value' => $record->getKey(),
    'label' => $record->name,
    'payload' => [
        'id' => $record->getKey(),
        'label' => $record->name,
        'extra_field' => $record->extra_field,
    ],
],
```

The helper ensures missing `id` or `label` entries are back-filled, but downstream code expects predictable keys – especially
when Alpine components mirror the payload into adjacent form fields.

## Clearing search inputs

Use `SearchableComponentHelper::clear()` to flush the component state and reset dependent form data when the lookup is emptied.
Pass one or more callbacks to erase related form keys via Filament’s `Set` utility:

```php
use Filament\Schemas\Components\Utilities\Set;

SearchableInput::make('shipping_address_lookup')
    ->afterStateUpdated(function (SearchableInput $component, ?int $state, Set $set): void {
        if ($state === null) {
            SearchableComponentHelper::clear(
                $component,
                static fn () => $set('shipping_address', []),
                static fn () => $set('shipping_method_id', null),
            );

            return;
        }

        // ...hydrate the address payload...
    });
```

Callbacks receive no arguments, so capture the `Set` instance (or any additional context) from the closure use clause. The helper
resets the component’s state, search options, and payload metadata before running your callbacks, ensuring the Livewire form
store stays consistent.

## Quick checklist

- Keep the payload structure aligned with the search service that powers the component. For example, `AddressSearch::payload()` already exposes the exact fields expected by the order form, so return it directly from your normaliser.
- When the component stores something other than the lookup identifier (for example, a composite key), make sure the `value` key reflects the final persisted state; the helper pushes that value back into the component before rendering.
- If a lookup fails or the state is empty, the helper automatically calls `clear()` so the UI stays in sync with the database.

## Resource integration checklist

- Register `afterStateHydrated` closures on your Filament form components to call `SearchableComponentHelper::hydrate()` with a finder closure and normaliser that return the `[value, label, payload]` tuple described above. This keeps edit forms and relation managers aligned when records are re-opened.
- Pair `afterStateUpdated` hooks with `SearchableComponentHelper::clear()` so clearing the lookup also wipes any dependent state (`Set` helpers for foreign keys, cached payload fields, and related dropdowns).
- Prefer returning a payload array that is already shaped for the downstream Livewire data structure you need. The helper simply forwards the normalised payload, making the component the single source of truth for metadata.

## Related guidelines

- Review the broader [searchable input metadata lifecycle](../forms/SEARCHABLE_INPUT_METADATA.md) for payload conventions and integration examples.
- Keep Filament resource ergonomics consistent by following the [navigation structure guide](../filament-navigation-structure.md) and the [navigation group compatibility rule](../filament-v4-navigation-group-rule.md).

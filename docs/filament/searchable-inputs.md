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

## Normalising payloads

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

- Always call the hydrator inside `afterStateHydrated()` so edits reopen with the correct label, option, and payload.
- Normalise payloads through the existing search helpers whenever they exist; otherwise, provide `id` and `label` keys manually.
- Use `clear()` whenever a lookup should wipe related fields. Keep the callbacks idempotent so repeated clears do not raise
  errors.

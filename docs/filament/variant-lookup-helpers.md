# Product Variant Lookup Helpers

Product variant search fields in Filament resources should reuse the `App\Support\Filament\ProductVariantFieldHelper` utility. Centralising this behaviour keeps hydration, clearing, and recalculation logic identical everywhere.

- **`hydrateSearchableVariant`** – repopulates the `SearchableInput` state/options during edit hydration so previously saved selections stay visible without extra boilerplate.
- **`handleVariantSelection`** – synchronises `product_variant_id`, the snapshot fields (`product_id`, `name`, `sku`, `unit_price`), and recalculates totals while guarding against empty lookups.

These helpers wrap the shared query (`id`, `product_id`, `sku`, `name`, `price` with the parent product eager-loaded) and defensive resets so that future variant pickers can stay concise.

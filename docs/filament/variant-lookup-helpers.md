# Product Variant Lookup Helpers

Product variant search fields in Filament resources should reuse the `App\Support\Filament\ProductVariantFieldHelper` utility. Centralising this behaviour keeps hydration, clearing, and recalculation logic identical everywhere while aligning with the reusable `SearchableComponentHelper` contract described in [docs/filament/searchable-inputs.md](searchable-inputs.md).

- **`hydrateSearchableVariant`** – repopulates the `SearchableInput` state/options during edit hydration so previously saved selections stay visible without extra boilerplate. Behind the scenes it calls `SearchableComponentHelper::hydrate()` with a normaliser that returns the `['value', 'label', 'payload']` tuple consumed by DefStudio's widget.
- **`handleVariantSelection`** – synchronises `product_variant_id`, the snapshot fields (`product_id`, `name`, `sku`, `unit_price`), and recalculates totals while guarding against empty lookups. When the lookup is cleared or the variant cannot be resolved it delegates to `SearchableComponentHelper::clear()` so dropdown options and payload metadata vanish alongside the dependent `Set` callbacks.

These helpers wrap the shared query (`id`, `product_id`, `sku`, `name`, `price` with the parent product eager-loaded) and defensive resets so that future variant pickers can stay concise while maintaining consistent payloads for totals recalculation.

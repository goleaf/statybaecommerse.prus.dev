# Searchable input metadata lifecycle

Filament search helpers in this project return deterministic `SearchResult` DTOs so downstream forms can hydrate related fields without extra queries. Each result exposes a primary value, display label, and arbitrary metadata payload, which the form layer reads when wiring dependent inputs.

## Normalised `SearchResult` shape

`App\\Filament\\Components\\AutocompleteSelect` caches every request as a collection of arrays shaped like `['value' => string, 'label' => string, 'data' => array<string, mixed>]`, keeping the DTO contract stable for Alpine and Livewire consumers.【F:app/Filament/Components/AutocompleteSelect.php†L29-L47】【F:app/Filament/Components/AutocompleteSelect.php†L181-L216】 Search services mirror that structure by attaching typed metadata keys to each result:

- `ProductSearch::complex()` stores identifiers, SKU, translated name, and numeric price so cart- and order-facing forms can populate sibling fields immediately.【F:app/Support/Search/ProductSearch.php†L33-L60】
- `ProductVariantSearch::results()` enriches variants with product-level identifiers and price data so repeaters know which variant-specific toggles to expose.【F:app/Support/Search/ProductVariantSearch.php†L33-L62】
- `AddressSearch::results()` packs a serialised payload alongside each hit, and `AddressSearch::payload()` exposes a consistent key/value map that can be injected straight into Filament `KeyValue` components.【F:app/Support/Search/AddressSearch.php†L47-L106】
- `ContentLinkSearch::results()` flags whether a record is static, product, category, collection, or post content, providing slugs and URLs so sliders and quick actions can persist canonical links.【F:app/Support/Search/ContentLinkSearch.php†L18-L252】

Stick to these keys (or extend them centrally in the search service) so downstream components receive the data they expect, and include any new fields in the corresponding service’s unit tests.

## Helper API for hydrating and clearing state

The Alpine helper embedded in `filament/components/autocomplete-select.blade.php` pushes the selected `SearchResult` payload into Livewire via `selectResult(result)` and removes it with `removeItem(item)` for multi-select fields. Both methods update the hidden form state so back-end hooks see the same payload Livewire receives.【F:resources/views/filament/components/autocomplete-select.blade.php†L21-L101】【F:resources/views/filament/components/autocomplete-select.blade.php†L108-L157】 When queries shrink below the minimum length, `performSearch()` clears cached results to avoid surfacing stale metadata from a previous lookup.【F:resources/views/filament/components/autocomplete-select.blade.php†L67-L89】

On the PHP side, defer to `App\Support\Filament\SearchableComponentHelper` so hydration and clearing logic stays centralised. The helper resolves the record, normalises it into a `[value, label, payload]` tuple, and pushes that shape back into the component while exposing optional callbacks for clearing related form state. See the [Searchable input helper usage](../filament/searchable-inputs.md) note for full examples and the expected normaliser contract.

Coordinate the server-side lifecycle with [`SearchableComponentHelper`](../filament/searchable-inputs.md). The helper centralises how Filament search inputs restore their state, inject options, and expose payload metadata, so reference it from your hydration and clearing closures instead of duplicating bespoke logic.

## Integration examples

The following resources already lean on the metadata payload to keep forms consistent:

- **Cart items** – selecting a product hydrates human-readable labels and unit price while clearing stale variant picks to avoid mismatched data.【F:app/Filament/Resources/CartItemResource.php†L16-L56】
- **Order addresses** – billing and shipping lookups project the stored address payload into editable key/value rows, or blank them entirely when the lookup is cleared.【F:app/Filament/Resources/OrderResource.php†L312-L354】
- **Wishlist items** – product lookups hydrate IDs and reset variant selectors, mirroring the cart workflow so storefront staff do not manage mismatched combinations.【F:app/Filament/Resources/WishlistItemResource.php†L160-L196】
- **Order items** – both the standalone resource and relation manager wrap variant lookups with `ProductVariantFieldHelper` so hydration, clearing, and total recalculation logic stay uniform across admin entry points.【F:app/Filament/Resources/OrderItemResource.php†L86-L225】【F:app/Filament/Resources/OrderResource/RelationManagers/OrderItemsRelationManager.php†L70-L165】

Replicate these patterns for any new searchable inputs so metadata remains authoritative and downstream automation (exports, cache warmers, printable documents) can depend on the enriched payload.

## Follow-up checklist

- [ ] Use `SearchableComponentHelper` whenever searchable inputs need to hydrate or clear server-side state.
- [ ] Request a team review of this document whenever the helper contract changes to keep the documentation accurate.

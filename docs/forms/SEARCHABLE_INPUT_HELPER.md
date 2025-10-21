# SearchableInput Helper Reference

The `App\\Support\\Filament\\Components\\SearchableComponentHelper` centralizes the repetitive wiring that our Filament `SearchableInput` fields need when hydrating existing records or clearing lookups.

## Available Utilities

- **`hydrateFromRecord()`** – accepts an already-loaded model and injects the correct option/state pair so Filament renders a selected value during form hydration.
- **`hydrateUsingResolver()`** – lazily resolves a model by identifier before deferring to `hydrateFromRecord()`. Use this whenever the resource only has an ID column at hydrate time.
- **`assignNullableId()`** – normalizes component state into nullable integer identifiers, ensuring blank states save as `null` instead of `0` or empty strings.
- **`syncLookupPayload()`** – keeps lookup components and their structured payloads (e.g., billing/shipping address metadata) in sync. When cleared, it resets both the lookup field and payload array to avoid stale UI state.

## Usage Notes

1. Always add a short inline comment referencing this page near helper-powered callbacks so future contributors know where to find behavioural details.
2. When a lookup controls derivative payload (addresses, contact payloads, etc.), wrap your domain-specific fetch logic in the resolver closure and return the normalized array (see `App\\Support\\Search\\AddressSearch::payload`).
3. Helper methods intentionally avoid emitting events; Filament's `Set` injection keeps dependent totals and key-value components in sync with the fresh payload data.

For metadata structure guidelines, continue to reference [`docs/forms/SEARCHABLE_INPUT_METADATA.md`](./SEARCHABLE_INPUT_METADATA.md).

# Change: Group Product Import Rows Into Variants

## Why
Product CSV imports currently treat each row as a standalone product, which duplicates parent products when the CSV represents multiple variants of the same item. Admins need repeated product names to resolve to one product with multiple variants.

## What Changes
- Update `ProductImporter` to resolve parent products by normalized product name (with sync keys still honored first when sync mode is enabled).
- Upsert `ProductVariant` rows during import so each CSV row can represent a variant with its own SKU, pricing, stock, and attributes.
- Extend variant-oriented CSV mapping support (`stock` alias, `volume`, `material`) while keeping existing product import behavior.
- Add feature tests covering same-name grouping and idempotent re-import by variant SKU.

## Impact
- Affected specs: `product-import-variants` (new capability)
- Affected code: `app/Filament/Imports/ProductImporter.php`, `tests/Feature/Filament/ProductImporterTest.php`

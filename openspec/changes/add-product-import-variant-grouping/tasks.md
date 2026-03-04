## 1. Implementation
- [x] 1.1 Extend `ProductImporter` to resolve parent products by normalized product name when sync keys do not match.
- [x] 1.2 Upsert `ProductVariant` rows per CSV row (SKU-first, fallback composite matching), including variant attributes and inventory/pricing values.
- [x] 1.3 Add importer column support for `stock` alias mapping and optional `volume` / `material` variant attributes.
- [x] 1.4 Keep parent product snapshot fields (price, stock, primary SKU) aligned with imported variants.
- [x] 1.5 Add feature tests for same-name grouping and SKU-based re-import idempotency.

## 2. Verification
- [x] 2.1 Run targeted product importer feature tests.
- [x] 2.2 Validate OpenSpec change with `openspec validate add-product-import-variant-grouping --strict`.

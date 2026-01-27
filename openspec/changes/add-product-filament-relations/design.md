# Design: Product Filament Relations

## Context
The admin surface expects dedicated Filament resources for product-adjacent models and relation managers within the Product resource. Existing tests assume these resources exist and support standard CRUD, filters, and stock-oriented list tabs.

## Approach
- Prefer simple, conventional Filament resources that directly map to Eloquent relationships and fields used by tests.
- Use relation managers on Product to surface key has-many relations without introducing new domain concepts.
- Align stock filters and tabs with `stock_quantity - reserved_quantity` when no variant inventory rows exist to keep admin list behavior deterministic in tests.

## Key Decisions
- Implement `ProductResource` relation managers rather than expanding the main product form to embed all related data.
- Keep resource forms minimal but complete enough to satisfy existing tests and admin workflows.
- Avoid schema changes; rely on existing migrations and models.
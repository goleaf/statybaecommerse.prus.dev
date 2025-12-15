# Requirements Document

## Introduction

This specification defines a performance-focused update for the egistatyba Laravel 12 + Livewire 3 storefront (notably `/{locale}` routes such as `https://egistatyba.test/lt`) and the supporting backend services. The goal is to improve perceived and measured performance (TTFB, query count, memory usage) without changing user-visible behaviour, SEO semantics, or business rules.

## Current Observations (Baseline Issues)

- The storefront currently returns **HTTP 500** because `App\Models\Product` implements `App\Contracts\TranslatableRecord` but does not implement `translations(): HasMany`, causing a fatal error at class load time. Performance work requires the application to boot reliably first.
- Locale resolution is duplicated across multiple Livewire components (`ensureLocale()` in `boot()` / `mount()` / `render()`), while `App\Http\Middleware\SetLocale` already runs for web requests. This increases CPU work and forces session writes/cookies on every request.
- Category facet counts (`brands`, `collections`, `categories`) in `app/Livewire/Pages/Category/Index.php` are computed with N+1 `COUNT(*)` queries (looping each facet entity and running a filtered count). This can create hundreds of queries on first render (even if cached after).
- The localized search page (`app/Livewire/Pages/Search.php`) performs `%term%` SQL `LIKE` scans across `products` and `product_translations`, bypassing the existing `App\Services\SearchService` (which already provides caching and optional Scout support).
- Several storefront list queries eager-load broad relations / columns when DTOs only need a small subset (e.g., category product listings select many `products.*` columns even though `ProductListItemData` consumes only a subset).

## Glossary

- **System**: The egistatyba Laravel 12 + Livewire 3 storefront application and supporting backend services.
- **TTFB**: Time to first byte (server response latency).
- **P95**: 95th percentile latency for a page/request.
- **Warm cache / Cold cache**: Responses served with caches already populated / empty.
- **N+1 queries**: A query pattern where 1 list query triggers an additional query per item.
- **DTO**: Data transfer object used to serialise minimal data (e.g., `ProductListItemData`).
- **Tag-aware cache**: Cache entries grouped by tags for selective invalidation (`TagAwareCache`, `CacheTags`).
- **Scout**: Laravel Scout search abstraction (optional, if configured).
- **Storefront**: The public-facing web interface accessible via `/{locale}` routes.
- **Facet counts**: Aggregate counts for filtering options (brands, collections, categories).

## Requirements

### Requirement 1: Application Must Boot Reliably Before Optimisation

**User Story:** As a developer, I want the application to boot without fatal errors, so that performance baselines can be measured and improvements validated.

#### Acceptance Criteria

1. WHEN requesting `GET /lt` THEN the System SHALL return `200` (or a valid redirect) and SHALL NOT produce a fatal error.
2. WHEN loading core storefront models (`Product`, `Brand`, `Collection`, `ProductVariant`) THEN the System SHALL satisfy all implemented interfaces (including `TranslatableRecord::translations()` where applicable).
3. WHEN the application fails to boot THEN the System SHALL emit a single actionable exception message (not cascading failures) to the application log.

### Requirement 2: Establish Measurable Performance Baselines

**User Story:** As a product owner, I want a baseline for page performance, so that improvements are measurable and regressions are detected early.

#### Acceptance Criteria

1. WHEN measuring baseline performance THEN the System SHALL record for each key storefront page: TTFB (P50/P95), total SQL query count, and peak memory.
2. WHEN measuring baseline performance THEN the System SHALL cover at least: `/{locale}` home, `/{locale}/categories`, `/{locale}/categories/{category}`, `/{locale}/products`, `/{locale}/products/{product}`, and `/{locale}/search?q=...`.
3. WHEN running in CI/test environment THEN the System SHALL provide automated assertions for query-count budgets for the pages/components updated in this initiative.

### Requirement 3: Remove Redundant Locale Resolution Work

**User Story:** As a user, I want localized pages to respond quickly, so that language switching does not slow down browsing.

#### Acceptance Criteria

1. WHEN serving any request in the `web` middleware group THEN locale resolution SHALL occur in a single, centralized place (middleware/service), not duplicated in multiple Livewire components.
2. WHEN the resolved locale does not change THEN the System SHALL NOT write to the session and SHALL NOT queue a new locale cookie on that request.
3. WHEN handling Livewire AJAX requests THEN the System SHALL preserve the locale consistently across requests without per-component locale re-initialization.

### Requirement 4: Eliminate N+1 Aggregate Queries on Category Filters

**User Story:** As a shopper, I want the category index filters to load instantly, so that filtering does not feel slow even with many brands/categories/collections.

#### Acceptance Criteria

1. WHEN rendering `/{locale}/categories` THEN facet counts for brands, collections, and categories SHALL be computed using aggregated queries (grouping) and SHALL NOT execute a query per facet option.
2. WHEN filters change (brand/collection/price/stock/search) THEN the System SHALL recompute facet counts with a bounded number of queries (target: ≤ 5 queries per request excluding pagination).
3. WHEN caches are warm THEN facet payload retrieval SHALL execute zero database queries.

### Requirement 5: Align Storefront Search With SearchService

**User Story:** As a shopper, I want search results to appear quickly and be relevant, so that I can find products without waiting.

#### Acceptance Criteria

1. WHEN performing a search on `/{locale}/search` THEN the System SHALL route search execution through `App\Services\SearchService` (or a single unified search layer).
2. WHEN Scout is enabled THEN the System SHALL use Scout-backed search; WHEN Scout is disabled THEN the System SHALL use a database fallback that is optimized for the production database engine.
3. WHEN searching with the same query repeatedly THEN the System SHALL serve results from cache (tagged by locale and catalog tags) within the configured TTL.

### Requirement 6: Reduce Storefront Payload Size and Hydration Cost

**User Story:** As a shopper, I want product lists to render smoothly, so that scrolling and pagination feel fast.

#### Acceptance Criteria

1. WHEN serving product list pages (home shelves, category show, catalog) THEN queries SHALL select only the fields required by the view/DTO and SHALL avoid loading large text blobs when not displayed.
2. WHEN a view iterates products THEN it SHALL NOT trigger lazy-loading queries for relations (brand, media, categories, translations).
3. WHEN returning cached list payloads THEN the cached values SHALL be serialisable DTOs/arrays (not full Eloquent models) where practical to reduce Livewire hydration overhead.

### Requirement 7: Cache Strategy Must Be Explicit and Invalidation-Safe

**User Story:** As an operator, I want caches to be predictable and safe, so that performance improves without serving stale data for too long.

#### Acceptance Criteria

1. WHEN caching storefront fragments THEN the System SHALL use tag-aware caching with consistent tags (`CacheTags::*`) so invalidation can be targeted (products/brands/categories/collections/locale/home/navigation).
2. WHEN catalog data changes (product/brand/category/collection updates) THEN the System SHALL invalidate relevant tags so storefront pages reflect updates within the expected window.
3. WHEN tags are supported by the configured cache driver THEN the System SHALL avoid redundant cache writes that double-write the same payload unless explicitly required for tests.

### Requirement 8: Database Indexing Must Support Storefront Query Patterns

**User Story:** As an operator, I want database queries to remain fast as data grows, so that performance does not degrade with catalog size.

#### Acceptance Criteria

1. WHEN reviewing storefront query patterns THEN the System SHALL have appropriate indexes for filtering/sorting (e.g., visibility + published date, brand/category pivots, translation lookups).
2. WHEN duplicate or redundant indexes exist (example: multiple `product_translations` locale/product_id indexes) THEN the System SHALL remove duplicates safely via migrations appropriate for the production database engine.
3. WHEN deploying schema changes THEN the System SHALL provide a rollback plan and SHALL not lock tables for an unacceptable duration (define acceptable duration per DB engine).

### Requirement 9: Production Runtime Configuration Must Support Performance

**User Story:** As an operator, I want production settings to be optimized, so that code-level improvements are not negated by configuration.

#### Acceptance Criteria

1. WHEN deploying to production THEN the System SHALL run framework optimisations (`config:cache`, `route:cache`, `view:cache`, `event:cache` where appropriate).
2. WHEN Redis (or equivalent) is available THEN the System SHALL support using it for cache and session to avoid database session write contention on high traffic pages.
3. WHEN queues are used (media conversions, emails, analytics) THEN the System SHALL ensure long-running work is offloaded from web requests.

### Requirement 10: Guardrails Against Performance Regression

**User Story:** As a developer, I want automated guardrails, so that future changes do not reintroduce slow queries or N+1 patterns.

#### Acceptance Criteria

1. WHEN running the test suite THEN the System SHALL include performance-oriented tests for the optimized pages/components (query count and cache usage assertions).
2. WHEN a regression exceeds the agreed budgets THEN CI SHALL fail with a clear message pointing to the offending page/component.
3. WHEN introducing new storefront features THEN developers SHALL add or update performance tests to preserve the defined budgets.


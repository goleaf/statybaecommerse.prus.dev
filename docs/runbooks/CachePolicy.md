# Cache Policy

This project standardizes cache key construction and lifetimes through the `App\Support\Cache\CacheKeys` helper. Keys are namespaced with colon delimiters so that related entries group naturally when inspected in Redis.

## Time-to-Live (TTL) Guide

| Helper constant | Duration | Typical usage |
| --- | --- | --- |
| `CacheKeys::TTL_MINUTE` | 60 seconds | Home page statistics, lightweight dashboard cards |
| `CacheKeys::TTL_TWO_MINUTES` | 120 seconds | High-churn dashboard activity feeds |
| `CacheKeys::TTL_FIVE_MINUTES` | 5 minutes | Home sliders, catalog dropdowns, collections |
| `CacheKeys::TTL_ONE_HOUR` | 1 hour | Featured products, popular categories, top brands |
| `CacheKeys::TTL_TWO_HOURS` | 2 hours | Reserved for medium-lived metrics or feed snapshots |
| `CacheKeys::TTL_SIX_HOURS` | 6 hours | Currency lists or other semi-static reference data |
| `CacheKeys::TTL_ONE_DAY` | 24 hours | Navigation trees and other rarely changing structures |

When the Cache API accepts a `DateTimeInterface`, prefer expressive helpers such as `now()->addMinutes(30)` alongside the centralized key builders.

## Tagging Conventions

Use the helper when attaching cache tags to keep invalidation consistent:

- `CacheKeys::productTag($productId)` → `product:{id}`
- `CacheKeys::categoryTag($categoryId)` → `category:{id}`
- `CacheKeys::brandTag($brandId)` → `brand:{id}`
- `CacheKeys::homeTag()` and `CacheKeys::dashboardTag()` for broad UI groupings
- `CacheKeys::productAggregateTag()` / `CacheKeys::userAggregateTag()` / `CacheKeys::orderAggregateTag()` for repository- or metric-level aggregates

### Cache tag helper

The dedicated `App\Support\Cache\CacheTags` class now generates locale-, resource-, and identifier-specific tag names. Prefer the helper when tagging caches:

- `CacheTags::locale($locale)` ensures translated storefront fragments clear correctly.
- `CacheTags::products()`, `CacheTags::categories()`, `CacheTags::brands()`, `CacheTags::collections()` scope invalidation to catalogue resources.
- `CacheTags::productIds([$id])`, `CacheTags::categoryIds([$id])`, etc., collapse identifier arrays into deterministic tag names for Livewire filters and show pages.

Tagging ensures that refreshing a product or category can invalidate related home and dashboard fragments without manual key enumeration.

## Invalidation Rules

- Product mutations should clear featured, trending, and navigation caches using the product/category/brand tags.
- Category structure changes must clear navigation trees (`CacheKeys::categoryNavigationTree()`) and home catalogue lookups.
- Dashboard metrics rely on short TTLs but still respect `CacheKeys::dashboardTag()` for forced refreshes during deployments.
- Product, user, and order observers flush the aggregate tags above on `created`, `updated`, `deleted`, `restored`, and `forceDeleted` so repository counts and dashboard snapshots never drift. When cache tags are unavailable (e.g. array store), the observers fall back to forgetting `CacheKeys::productTotalCount()`, `CacheKeys::userTotalCount()`, and the locale-aware dashboard metric keys.

Where Redis tags are unavailable, fall back to targeted `Cache::forget()` calls that leverage the centralized builders.

## Repository & Dashboard Metrics

- `App\Repositories\ProductRepository::count()` and `App\Repositories\UserRepository::count()` cache totals for the default connection using `CacheKeys::productTotalCount()` / `CacheKeys::userTotalCount()` with aggregate + dashboard tags. Passing a non-default connection bypasses caching to keep verification workflows accurate.
- `App\Services\Dashboard\DashboardMetricsRepository` now tags the fast-path metrics:
  - `orders_today` & `revenue_last_seven_days` → `CacheKeys::orderAggregateTag()`
  - `new_users_today` → `CacheKeys::userAggregateTag()`
  - `low_stock_items` → `CacheKeys::productAggregateTag()`
- The observers described above provide deterministic invalidation so cached values refresh immediately after underlying data changes.

### Livewire storefront fragments

- `CacheKeys::categoryIndex*` builders scope cache entries for the category listing page. Pair them with `CacheTags::brandIds()`, `CacheTags::collectionIds()`, and `CacheTags::categoryIds()` so filters stay in sync with catalogue updates.
- `CacheKeys::categoryShowProducts()` caches paginated product responses per locale, page, and sort mode; tags include the specific category id for targeted flushes.
- `CacheKeys::productDetail()`, `CacheKeys::productRecentHistories()`, and `CacheKeys::productRecentReviews()` wrap the Single Product page queries. Tag them with `CacheTags::products()` and `CacheTags::productIds()` so product updates invalidate all related fragments.

## Extending `CacheKeys`

1. Add a descriptive builder method in `app/Support/Cache/CacheKeys.php`.
2. Reuse existing private helpers (e.g., `homeKey`) to preserve naming patterns.
3. Document new TTL expectations or tag usage inside this policy file.
4. Update dependent services or components to consume the new helper before removing legacy keys.

Keeping key composition centralized makes future refactors predictable and prevents cache collisions across modules.

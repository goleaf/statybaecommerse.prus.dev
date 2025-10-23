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

Prefer the dedicated `App\Support\Cache\CacheTagHelper` when attaching cache tags so the invalidation service can target entire feature areas with a single flush:

- `CacheTagHelper::products()` → storefront product tiles, featured shelves, related product lists.
- `CacheTagHelper::categories()` → navigation menus, category pickers, catalogue landing pages.
- `CacheTagHelper::brands()` → brand carousels, filters, and top brand showcases.
- `CacheTagHelper::collections()` → collection sliders and Livewire showcases.
- `CacheTagHelper::dashboards()` → Livewire dashboard widgets, Filament overview cards, and reporting caches.

Legacy helpers (`CacheKeys::productTag($id)`, `CacheKeys::homeTag()`, and similar) remain available when a specific key needs to be targeted, but feature-level tagging should always include the `CacheTagHelper` group so global flushes stay predictable.

## Invalidation Rules

- The `App\Services\CacheInvalidationService` is the single entry point for cache flushing. Observers and service hooks call `flushForModel()` so changing a `Product`, `Category`, `Brand`, or `Collection` automatically clears the correct tag groups.
- Product mutations therefore clear featured, trending, navigation, and dashboard caches through the shared product tag group. Category mutations flush category and related product caches, and so on.
- Dashboard metrics rely on short TTLs but still receive `CacheTagHelper::dashboards()` so forced refreshes happen instantly during deployments or data imports.
- When cache tags are unavailable (for example, while using the array driver) the invalidation service logs the issue and falls back to targeted key resets. Product events clear the `home:*` shelves, featured lists, and related product entries per locale and currency, while categories, brands, and collections drop their respective navigation trees and showcase caches.

Where Redis tags are unavailable, fall back to targeted `Cache::forget()` calls that leverage the centralized builders.

## Repository & Dashboard Metrics

- `App\Support\Repositories\ProductRepository::count()` and `App\Support\Repositories\UserRepository::count()` cache totals for the default connection using `CacheKeys::productTotalCount()` / `CacheKeys::userTotalCount()` with aggregate + dashboard tags. Passing a non-default connection bypasses caching to keep verification workflows accurate.
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

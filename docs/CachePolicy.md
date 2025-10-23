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

- Product mutations should clear featured, trending, and navigation caches using the product/category/brand tags.
- Category structure changes must clear navigation trees (`CacheKeys::categoryNavigationTree()`) and home catalogue lookups.
- Dashboard metrics rely on short TTLs but still respect `CacheKeys::dashboardTag()` for forced refreshes during deployments.
- Product, user, and order observers flush the aggregate tags above on `created`, `updated`, `deleted`, `restored`, and `forceDeleted` so repository counts and dashboard snapshots never drift. When cache tags are unavailable (e.g. array store), the observers fall back to forgetting `CacheKeys::productTotalCount()`, `CacheKeys::userTotalCount()`, and the locale-aware dashboard metric keys.
- The `CacheInvalidationService` coordinates fallback invalidation when drivers do not support tags by clearing home shelves, collection showcases, navigation menus, and dashboard metrics via `Cache::forget()` calls that leverage the helper builders. This prevents us from flushing the entire cache store when using the array/file driver while still keeping storefront widgets in sync.

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

### Filament resource alignment

- Keep admin resources in sync with Filament v4 expectations by returning `Form` and `Table` instances directly from the `form()` and `table()` methods.
- Document icon usage with PHPDoc annotations when relying on enum-backed Heroicons so cache warmers can reason about icon metadata consistently.
- Audit legacy resources such as `AddressResource` whenever phpstan surfaces signature mismatches; updating them to the strict `Form`/`Table` return types keeps hook-driven cache warmers runnable during deployments.

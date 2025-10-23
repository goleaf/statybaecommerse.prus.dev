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

Tagging ensures that refreshing a product or category can invalidate related home and dashboard fragments without manual key enumeration.

## Invalidation Rules

- Product mutations should clear featured, trending, and navigation caches using the product/category/brand tags.
- Category structure changes must clear navigation trees (`CacheKeys::categoryNavigationTree()`) and home catalogue lookups.
- Dashboard metrics rely on short TTLs but still respect `CacheKeys::dashboardTag()` for forced refreshes during deployments.

Where Redis tags are unavailable, fall back to targeted `Cache::forget()` calls that leverage the centralized builders.

## Extending `CacheKeys`

1. Add a descriptive builder method in `app/Support/Cache/CacheKeys.php`.
2. Reuse existing private helpers (e.g., `homeKey`) to preserve naming patterns.
3. Document new TTL expectations or tag usage inside this policy file.
4. Update dependent services or components to consume the new helper before removing legacy keys.

Keeping key composition centralized makes future refactors predictable and prevents cache collisions across modules.

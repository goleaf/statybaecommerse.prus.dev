# Feature Highlights

## Fulfilment & Logistics
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.

## Admin panel resilience
- Filament navigation icons once again use docblock overrides so enum-aware navigation metadata loads without typed property collisions introduced in PR #1098.
- Variant stock history tables consolidate destructive change reasons under the `danger` badge, keeping badge colors predictable for both `damage` and `theft` events.

## Tooling polish
- The `data:import` Artisan command now documents its signature and description directly on the command class, improving discoverability via `php artisan list`.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
- Filament analytics utilities reference the updated User Product Interaction resource so schema contract mismatches no longer block admin boot sequences.

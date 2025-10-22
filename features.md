# Feature Highlights

## Fulfilment & Logistics
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.

## Admin panel resilience
- Test bootstrapping now adds both JSON translation directories so Filament's commerce navigation label renders localized copy during PHPUnit runs.
- Filament navigation icons once again use docblock overrides so enum-aware navigation metadata loads without typed property collisions introduced in PR #1098.
- Variant stock history tables consolidate destructive change reasons under the `danger` badge, keeping badge colors predictable for both `damage` and `theft` events.
- User Product Interaction analytics pages restore Filament v4-friendly spacing for interaction filters and rating badges, silencing the concatenation notices flagged while QAing PR #1097.

## Caching & performance
- Cache invalidation conflicts from PR #120 are closed: navigation/menu caches now rely on the shared tag helper, model events invoke the invalidation service automatically, and new storefront/dashboard regression tests confirm cached payloads refresh right after catalogue updates.

## Content safety and compliance
- Established an allow-listed HTML sanitizer that runs on product descriptions, translations, and legal documents to prevent script injection.
- Added a storefront `<x-sanitized-html>` component so any rendered rich text automatically passes through the sanitizer.
- Shipped the `php artisan maintenance:sanitize-html` command to reprocess legacy content in bulk.

## Tooling polish
- Weekly Dependabot runs now open dependency pull requests pre-labeled for triage and auto-merge when safe, tightening our update cadence without manual babysitting.
- `scripts/upgrade_filament_schema.php` now updates navigation icon docblocks automatically while refactoring `form`, `infolist`, and `table` signatures, making repeated schema migrations safe for the entire Filament tree.
- The `data:import` Artisan command now documents its signature and description directly on the command class, improving discoverability via `php artisan list`.

## Documentation consolidation
- Documentation now lives in dedicated `docs/analysis/`, `docs/runbooks/`, and `docs/contracts/` directories, with a new [style guide](docs/STYLE_GUIDE.md) and CI guard ensuring Markdown stays reviewable.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
- Filament analytics utilities reference the updated User Product Interaction resource so schema contract mismatches no longer block admin boot sequences.

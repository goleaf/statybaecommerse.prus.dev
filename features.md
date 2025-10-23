# Feature Highlights

## Fulfilment & Logistics
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.

## Admin panel resilience
- Filament resources, relation managers, and admin-only pages now target the v4 Schema API with normalized navigation icon docblocks, preserving enum-aware metadata resolution across the upgraded form, table, and infolist builders.
- Notification resource navigation now delegates to the central Nav registry with an explicit recursion guard, and Address forms explain their `Schema::components([...])` container pipeline for Filament reviewers.
- Menu Item configuration now leans on the shared navigation icon docblock and clarifies the schema/table configurators so reviewers immediately see how the Filament resource delegates to reusable builders.
- Wishlist Item management now uses the Filament static navigation icon property with an explicit sidebar sort comment so customer tooling stays grouped predictably in the admin.
- Activity Log monitoring now declares its navigation icon with the BackedEnum-friendly union type mandated by Filament v4, keeping the admin panel boot sequence stable.
- Feature Flag listings now bypass the active and enabled scopes so administrators can review inactive toggles alongside live ones without temporary scope adjustments.
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

## Security hardening
- Documented the open proposal in PR #289 to layer per-user and per-IP throttling buckets across read, write, notification, and autocomplete APIs with correlation-aware logging so security reviewers can coordinate the upcoming rollout.
- Request-scoped CSP nonces now propagate through middleware, helpers, Livewire, and Vite so every inline Blade script/style satisfies the stricter nonce-based CSP and updated HSTS/permissions policy headers.

## Tooling polish
- Husky Git hook bootstrap regained its full shim after the deprecation banner replacement, keeping the executable wrapper in place so local hooks continue to run with the repository binaries.
- `scripts/upgrade_filament_schema.php` now updates navigation icon docblocks automatically while refactoring `form`, `infolist`, and `table` signatures, making repeated schema migrations safe for the entire Filament tree.
- The `data:import` Artisan command now documents its signature and description directly on the command class, improving discoverability via `php artisan list`.
- Legacy diagnostics artisan commands were retired in favour of PHPUnit suites guarded by a configurable coverage extension and Paratest-aware composer scripts, making quality checks part of every test run.

## API experience
- Product search, catalogue, and detail endpoints resolve via dedicated application use cases, an Eloquent-backed repository, and a presenter that preserves the public contract while filtering hidden or malformed catalogue entries.
- Problem+JSON responses now include the shared `error.rate_limited` code for HTTP 429 throttling scenarios, helping integrators react uniformly when the throttle middleware triggers.

## Documentation consolidation
- Documentation now lives in dedicated `docs/analysis/`, `docs/runbooks/`, and `docs/contracts/` directories, with a new [style guide](docs/STYLE_GUIDE.md) and CI guard ensuring Markdown stays reviewable.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
- Filament analytics utilities reference the updated User Product Interaction resource so schema contract mismatches no longer block admin boot sequences.

# Platform Features

## Fulfilment & Logistics
- Orders and zones now rely on first-class shipping option relationships, so factories and regression tests can link carriers
  without hand-setting foreign keys.
- Stock reservation migrations now create their tables before wiring foreign keys, so full database refreshes finish cleanly while live environments keep cascading deletes for products and variant inventories.
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.
- Shipping option entities once again declare their zone relationship, eligibility checks, and filter scopes, ensuring fulfilment automation and tests can target the correct carrier records without custom query shims.

## Data integrity & seeding
- Test harness provisioning now uses an on-disk SQLite database and metadata-aware factories, preventing the user attribution observer tests from failing with missing tables or JSON columns.
- Orders now seed using the broadened `orders.status` enum (covering confirmed/completed/returned) so MySQL no longer truncates demo data during `php artisan migrate:fresh --seed` and admin analytics stay in sync.
- Data import tooling keeps its foreign key safety net covered in tests by invoking the protected truncation helper via reflection, allowing the final Artisan command to remain sealed while still exercising failure recovery.
- Created_at index migrations now detect pre-existing keys case-insensitively (with driver-specific fallbacks) and the demo currency/country seeds align with multilingual schemas, so `php artisan migrate:fresh --seed` completes reliably across supported databases.
- Discount rebuild migrations now temporarily relax foreign key checks only while replaying legacy rows, preventing the `discount_codes_created_by_foreign` MySQL error during full refreshes without sacrificing referential integrity.
- User and author foreign keys on rebuilt discount tables now attach after verifying the `users` table compatibility, ensuring MySQL restores with mixed storage engines keep migrating without tripping the `discount_codes_created_by_foreign` system-table check.
- Focused PHPUnit runs now auto-run pending migrations when the in-memory SQLite database is empty, keeping factories from hitting missing-table errors while generating deterministic email addresses.

## Storefront discovery
- Search endpoint hardening now rejects suspicious SQL fragments and adds an explicit exact-match boost so precise catalogue queries surface first and malicious payloads return empty buckets.
- Search type filters now normalise mixed-case identifiers from clients, ensuring storefront queries stay restricted to the requested product, category, or brand buckets instead of ballooning to every result group.

## Admin panel resilience
- Reintroduced the core `App\\Exceptions\\Handler` class so the application reports exceptions normally instead of crashing with `Whoops\\Run::handleShutdown()` during bootstrap.
- Customer and product sparkline widgets now reuse the cached analytics series and expose matching dataset checksums, ensuring inline charts and regression tests evaluate the same trends.
- The custom Edit Profile page now imports `Filament\\Schemas\\Schema`, keeping the authentication profile form aligned with v4 expectations and preventing namespace-related fatal errors during automated test cycles.
- Discount Redemption navigation now lives under the Marketing group with a warning badge and Filament v4 badge styling, and the Pest harness includes a HasTable-aware stub so table schemas are exercised reliably in unit tests.
- Pest test bootstrap helpers now guard the `login()`, `get()`, and `post()` helpers with function-existence checks so repeated includes during `php artisan test` runs no longer trigger fatal redeclaration errors.
- Added a foundational `customer_groups` migration so later schema updates (extra permissions, soft deletes, translations) apply cleanly during `php artisan migrate:fresh --seed` runs.
- Filament resources, relation managers, and admin-only pages now target the v4 Schema API with normalized navigation icon docblocks, preserving enum-aware metadata resolution across the upgraded form, table, and infolist builders.
- Analytics event dashboards and rate limiting diagnostics now skip restrictive scopes in console contexts, fall back to raw configuration when container bindings are missing, and unhide disabled brands, keeping Pest unit suites and Filament tooling reliable during maintenance.
- Navigation icons and navigation groups across every Filament resource, relation manager, widget, and custom page now declare the BackedEnum/UnitEnum union types required by v4, keeping PHP 8.3 installs from triggering property type fatals during admin boot.
- Notification resource navigation now delegates to the central Nav registry with an explicit recursion guard, and Address forms explain their `Schema::components([...])` container pipeline for Filament reviewers.
- Menu Item configuration now leans on the shared navigation icon docblock and clarifies the schema/table configurators so reviewers immediately see how the Filament resource delegates to reusable builders.
- Wishlist Item management now uses the Filament static navigation icon property with an explicit sidebar sort comment so customer tooling stays grouped predictably in the admin.
- Activity Log monitoring now declares its navigation icon with the BackedEnum-friendly union type mandated by Filament v4, keeping the admin panel boot sequence stable.
- Feature Flag listings now bypass the active and enabled scopes so administrators can review inactive toggles alongside live ones without temporary scope adjustments.
- Test bootstrapping now adds both JSON translation directories so Filament's commerce navigation label renders localized copy during PHPUnit runs.
- Filament navigation icons once again use docblock overrides so enum-aware navigation metadata loads without typed property collisions introduced in PR #1098.
- Analytics dashboards now declare navigation metadata via the shared docblock convention and keep the resource form signature aligned with Filament v4 expectations, preventing BackedEnum type collisions during admin boot.
- Variant stock history tables consolidate destructive change reasons under the `danger` badge, keeping badge colors predictable for both `damage` and `theft` events.
- User Product Interaction analytics pages restore Filament v4-friendly spacing for interaction filters and rating badges, silencing the concatenation notices flagged while QAing PR #1097.

## Storefront experience
- The localized search page now opens with a guided hero, live result metrics, and improved empty states so shoppers can refine Makita-grade queries without leaving the results screen.

## Caching & performance
- Cache invalidation conflicts from PR #120 are closed: navigation/menu caches now rely on the shared tag helper, model events invoke the invalidation service automatically, and new storefront/dashboard regression tests confirm cached payloads refresh right after catalogue updates.
- Storefront autocomplete reuses injected cache and highlighting services, trims whitespace-only queries, and sanitizes highlight payloads so results load faster without leaking `<mark>` tags into the suggestion UI.

## Content safety and compliance
- Established an allow-listed HTML sanitizer that runs on product descriptions, translations, and legal documents to prevent script injection.
- The sanitizer now removes entire `<script>`, `<style>`, and `<template>` elements instead of leaving their inline payloads behind, keeping sanitized storefront and admin content free from executable remnants.
- Added a storefront `<x-sanitized-html>` component so any rendered rich text automatically passes through the sanitizer.
- Shipped the `php artisan maintenance:sanitize-html` command to reprocess legacy content in bulk.

## Security hardening
- Documented the open proposal in PR #289 to layer per-user and per-IP throttling buckets across read, write, notification, and autocomplete APIs with correlation-aware logging so security reviewers can coordinate the upcoming rollout.
- Request-scoped CSP nonces now propagate through middleware, helpers, Livewire, and Vite so every inline Blade script/style satisfies the stricter nonce-based CSP and updated HSTS/permissions policy headers.

## Tooling polish
- `scripts/upgrade_filament_schema.php` now updates navigation icon docblocks automatically while refactoring `form`, `infolist`, and `table` signatures, making repeated schema migrations safe for the entire Filament tree.
- The `data:import` Artisan command now documents its signature and description directly on the command class, improving discoverability via `php artisan list`.
- Legacy diagnostics artisan commands were retired in favour of PHPUnit suites guarded by a configurable coverage extension and Paratest-aware composer scripts, making quality checks part of every test run.

## API experience
- Product search, catalogue, and detail endpoints resolve via dedicated application use cases, an Eloquent-backed repository, and a presenter that preserves the public contract while filtering hidden or malformed catalogue entries.
- Problem+JSON responses now include the shared `error.rate_limited` code for HTTP 429 throttling scenarios, helping integrators react uniformly when the throttle middleware triggers.
- Audit log listings now fall back to descending IDs when timestamps match so follow-up mutations appear ahead of their initial creation event, keeping paginated reviews consistent for admins and API consumers.

## Documentation consolidation
- Documentation now lives in dedicated `docs/analysis/`, `docs/runbooks/`, and `docs/contracts/` directories, with a new [style guide](docs/STYLE_GUIDE.md) and CI guard ensuring Markdown stays reviewable.

## Project governance
- Maintainer playbooks now capture the Oct 21–22, 2025 PR triage in `docs/analysis/CURRENT_SYSTEM_STATUS.md`, calling out immediate merges, superseded branches, and fix-required submissions so review queues stay actionable without reprocessing GitHub filters.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
- Filament analytics utilities reference the updated User Product Interaction resource so schema contract mismatches no longer block admin boot sequences.

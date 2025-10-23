# Platform Features

## Fulfilment & Logistics
- Orders and zones now rely on first-class shipping option relationships, so factories and regression tests can link carriers
  without hand-setting foreign keys.
- Stock reservation migrations now create their tables before wiring foreign keys, so full database refreshes finish cleanly while live environments keep cascading deletes for products and variant inventories.
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.
- Shipping option entities once again declare their zone relationship, eligibility checks, and filter scopes, ensuring fulfilment automation and tests can target the correct carrier records without custom query shims.

## Pricing & Currency
- Filament currency administration now accepts extended ISO identifiers, surfaces inactive entries for auditing, and respects per-currency separators when rendering formatted amounts.

## Data integrity & seeding
- Products now persist the expanded status enum by migrating the column to a string-based implementation, allowing values like `active` to survive fresh installs and SQLite-powered test suites without CHECK constraint failures.
- Demo store seeder now calls the collection and collection-product seeders, ensuring curated collections always feature representative products during fresh installs and automated demos.
- PHPUnit harness now provisions a shared `database/testing.sqlite` file, runs a focused SQLite migration that seeds Spatie permission tables and variant attribute pivots, and registers Filament SearchableInput payload macros so suites share deterministic schema state without losing compatibility.
- Region hierarchies and the `customers` table now provision automatically during SQLite migrations, ensuring factories, analytics widgets, and Filament resources can persist customer journeys without manual schema patches.
- Customer group factories now benefit from automatic slug generation driven by the group code or localized name, preventing SQLite NOT NULL failures during tests while keeping storefront targeting identifiers predictable.
- Parallel test workers now receive dedicated SQLite databases derived from Laravel's `ParallelTesting::token()`, preventing file locks, and the SearchableInput payload macros register lazily so hydrate/clear helpers operate even when the service provider has not pre-booted them.
- Test harness provisioning now uses an on-disk SQLite database and metadata-aware factories, preventing the user attribution observer tests from failing with missing tables or JSON columns.
- Orders now seed using the broadened `orders.status` enum (covering confirmed/completed/returned) so MySQL no longer truncates demo data during `php artisan migrate:fresh --seed` and admin analytics stay in sync.
- Data import tooling keeps its foreign key safety net covered in tests by invoking the protected truncation helper via reflection, allowing the final Artisan command to remain sealed while still exercising failure recovery.
- Created_at index migrations now detect pre-existing keys case-insensitively (with driver-specific fallbacks) and the demo currency/country seeds align with multilingual schemas, so `php artisan migrate:fresh --seed` completes reliably across supported databases.
- Order analytics scopes now hint the standalone created-at index, the orders table seeds that index on fresh installs, and the refined ActiveScope lets diagnostics seeders retain processing orders for regression coverage.
- Discount rebuild migrations now temporarily relax foreign key checks only while replaying legacy rows, preventing the `discount_codes_created_by_foreign` MySQL error during full refreshes without sacrificing referential integrity.
- User and author foreign keys on rebuilt discount tables now attach after verifying the `users` table compatibility, ensuring MySQL restores with mixed storage engines keep migrating without tripping the `discount_codes_created_by_foreign` system-table check.
- User-product interaction factories now default to alternate interaction types
  and constrained timestamp windows, while the model enforces lean fillable
  data and scope-free product relations so duplicate analytics records no
  longer collide with unique keys during regression tests.

## Admin UI & Search
- Filament autocomplete select components now discard model global scopes during lookups so catalog managers immediately see freshly created records in suggestion lists while still benefiting from cached, trimmed queries.

## Discounts & promotions
- Coupon Usage administration now exposes the standard delete action and an exact-date filter that also accepts Livewire's `filterTable()` strings, keeping the Filament UI and regression coverage perfectly aligned.
- Coupon migrations now provision maximum discount caps, per-user usage limits, and product/category scoping columns so factories, admin forms, and API tests share the same schema snapshot during refreshes.
- Coupon application responses now round computed totals instead of calling `Number::parseFloat` on floats, keeping the discount API stable across PHP 8.3 test runs.

## Storefront discovery
- Cache invalidation service now flushes featured collections, navigation menus, and live dashboard stats across tagged cache stores and Livewire components whenever catalogue data changes, keeping showcases accurate during edits.
- Brands directory was redesigned with a light-themed layout, shared card components, and refreshed translations for English and Lithuanian so partner browsing feels polished across locales.
- Search endpoint hardening now rejects suspicious SQL fragments and adds an explicit exact-match boost so precise catalogue queries surface first and malicious payloads return empty buckets.
- Search type filters now normalise mixed-case identifiers from clients, ensuring storefront queries stay restricted to the requested product, category, or brand buckets instead of ballooning to every result group.
- Catalogue search now rebuilds product metrics from related tables, honours the `product_categories` pivot, and tolerates Redis being unavailable so the API behaves consistently across MySQL and SQLite deployments.

## API contracts
- OpenAPI documentation now mirrors the lean product meta payload and nullable media thumbnails emitted by the presenter, keeping schema validators and client SDKs in sync with production responses.

## Admin panel resilience
- Campaign conversion HTTP fallbacks now expose filterable HTML, verification toggles, and CSV exports so HTTP feature tests can assert outcomes without booting the Filament Livewire stack.
- Attribute administration keeps validation rule strings verbatim, surfaces array-based rules as comma-separated chips, and pairs with regression tests that prove both paths round-trip correctly through Filament.
- Attribute group filters, columns, and form selectors now share a translation fallback so legacy group slugs render as readable labels instead of raw keys throughout the Filament admin.
- Filament dashboard access checks now fall back to open access when no
  permissions are configured and inline sparkline widgets respect the base
  nullable model contract, eliminating the latest regression tests failures.
- Campaign customer segment listings now use local scopes for segment type,
  campaign, customer group, and activity filters so admins can intentionally
  include inactive rows in reviews without disabling visibility controls for
  every query.
- Document Template resource now aligns with Filament v4 schema components, strips editor wrappers before persisting content, and seeds enum-backed factories so CRUD forms, filters, and document relationships behave consistently in tests and production.
- System setting translation management once again supports soft deletes, restores, and safe duplication because the locale index has been relaxed and the fillable contract mirrors the documented API fields.
- Campaign conversion analytics drop the inherited ActiveScope so the model's
  own status-aware scopes (campaign, type, device, etc.) surface completed
  conversions for marketing dashboards and unit coverage without extra query
  overrides.
- Toggleable table layout tests now restore the global RefreshDatabase state after seeding bespoke tables and the news category factory defaults to visible records, keeping PHPUnit suites from skipping migrations and ensuring scoped relationships exist for admin coverage.
- Cart lifecycle unit coverage now builds a dedicated lightweight `cart_items` table per test so checkout cleanup behaviours stay validated without depending on the entire migration suite.
- Campaign click factories now guard related lookups and PHPUnit targets the shared SQLite database file, eliminating the missing-table exceptions that previously interrupted API campaign listing tests during fresh runs.
- Reintroduced the core `App\\Exceptions\\Handler` class so the application reports exceptions normally instead of crashing with `Whoops\\Run::handleShutdown()` during bootstrap.
- Customer and product sparkline widgets now reuse the cached analytics series and expose matching dataset checksums, ensuring inline charts and regression tests evaluate the same trends.
- The custom Edit Profile page now imports `Filament\\Schemas\\Schema`, keeping the authentication profile form aligned with v4 expectations and preventing namespace-related fatal errors during automated test cycles.
- Company model unit tests now provision the `companies` schema during setup and relax the global active scope until migrations run, so SQLite suites no longer fail with missing-table errors.
- Pest test bootstrap helpers now guard the `login()`, `get()`, and `post()` helpers with function-existence checks so repeated includes during `php artisan test` runs no longer trigger fatal redeclaration errors.
- Address autocomplete builders now emit top-level metadata and skip user-owned scopes, keeping admin search suggestions accurate for support agents who are not impersonating a specific shopper.
- Added a foundational `customer_groups` migration so later schema updates (extra permissions, soft deletes, translations) apply cleanly during `php artisan migrate:fresh --seed` runs.
- Filament resources, relation managers, and admin-only pages now target the v4 Schema API with normalized navigation icon docblocks, preserving enum-aware metadata resolution across the upgraded form, table, and infolist builders.
- Analytics event dashboards and rate limiting diagnostics now skip restrictive scopes in console contexts, fall back to raw configuration when container bindings are missing, and unhide disabled brands, keeping Pest unit suites and Filament tooling reliable during maintenance.
- Navigation icons and navigation groups across every Filament resource, relation manager, widget, and custom page now declare the BackedEnum/UnitEnum union types required by v4, keeping PHP 8.3 installs from triggering property type fatals during admin boot.
- Notification resource navigation now delegates to the central Nav registry with an explicit recursion guard, and Address forms explain their `Schema::components([...])` container pipeline for Filament reviewers.
- Admin user verification tooling now hydrates `email_verified_at` via fillable attributes, unlocks bulk verification actions to access their selected records, and freezes Pest timestamps so regression coverage confirms deterministic Filament behaviour.
- Menu Item configuration now leans on the shared navigation icon docblock and clarifies the schema/table configurators so reviewers immediately see how the Filament resource delegates to reusable builders.
- Wishlist Item management now uses the Filament static navigation icon property with an explicit sidebar sort comment so customer tooling stays grouped predictably in the admin.
- Activity Log monitoring now declares its navigation icon with the BackedEnum-friendly union type mandated by Filament v4, keeping the admin panel boot sequence stable.
- Feature Flag listings now bypass the active and enabled scopes so administrators can review inactive toggles alongside live ones without temporary scope adjustments.
- Attribute Value management now ignores the active/enabled global scopes in
  the Filament resource, letting administrators toggle availability, set
  defaults, and bulk-activate options without reaching for raw database
  queries.
- Test bootstrapping now adds both JSON translation directories so Filament's commerce navigation label renders localized copy during PHPUnit runs.
- The PHPUnit harness now seeds a dedicated SQLite testing database and directs Telescope/Activity Log to that connection before migrations execute, so catalog integrity and other schema-heavy suites run without missing table errors.
- Filament navigation icons once again use docblock overrides so enum-aware navigation metadata loads without typed property collisions introduced in PR #1098.
- Analytics dashboards now declare navigation metadata via the shared docblock convention and keep the resource form signature aligned with Filament v4 expectations, preventing BackedEnum type collisions during admin boot.
- Campaign conversion metrics now bypass the generic ActiveScope, expose their translation model, and seed fresh conversion timestamps so analytics widgets and unit tests agree on which records qualify for ROI and ROAS summaries.
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
- Contract validation endpoints now mirror the published schemas thanks to the persistent SQLite test bootstrap and trimmed response envelopes, ensuring the API suite passes on fresh installs.
- Product search, catalogue, and detail endpoints resolve via dedicated application use cases, an Eloquent-backed repository, and a presenter that preserves the public contract while filtering hidden or malformed catalogue entries.
- Problem+JSON responses now include the shared `error.rate_limited` code for HTTP 429 throttling scenarios, helping integrators react uniformly when the throttle middleware triggers.
- Validation problem responses now deliver localized violation arrays plus a fallback English reason so clients can show consistent messaging while still exposing locale-specific details.
- Access denied HTTP exceptions now keep their explicit denial reason inside `error.context.reason`, aligning Symfony-generated responses with Laravel's authorization handler contract.

## Documentation consolidation
- Documentation now lives in dedicated `docs/analysis/`, `docs/runbooks/`, and `docs/contracts/` directories, with a new [style guide](docs/STYLE_GUIDE.md) and CI guard ensuring Markdown stays reviewable.

## Project governance
- Maintainer playbooks now capture the Oct 21–22, 2025 PR triage in `docs/analysis/CURRENT_SYSTEM_STATUS.md`, calling out immediate merges, superseded branches, and fix-required submissions so review queues stay actionable without reprocessing GitHub filters.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
- Filament analytics utilities reference the updated User Product Interaction resource so schema contract mismatches no longer block admin boot sequences.

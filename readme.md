# Statybos E-commerce Platform

This consolidated README merges the previously separate `README.md`, `FEATURES.md`, `features.md`, `CHANGELOG.md`, and `channgelog.md` files into a single documentation hub. All historical notes, feature narratives, and changelog entries now live here for a single source of truth.

[![CI](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml/badge.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/badge/coverage-manual--run-lightgrey.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)

## Python scripts

Below are run and test commands for the project's Python scripts.

### How to Run

```bash
python main.py
```

### How to Test

```bash
pytest test_main.py
```

### Recent Maintenance

- Autocomplete selects in the Filament admin now bypass model-level global scopes when searching so freshly created products and supporting records appear immediately in the dropdown suggestions.

## What it is

A multilingual Laravel 12 + Filament v4 storefront and admin panel for managing construction-product catalogues, analytics, and operations for statybaecommerse.prus.dev. The repository ships extensive Filament resources, analytics dashboards, and seeders so you can explore the platform locally without extra setup.

### Key capabilities at a glance

- **Product catalogue management** with rich attribute, variant, bundle, and availability tooling powered by Filament resources.
- **Customer- and order-centric workflows** including loyalty, referral, and recommendation engines surfaced through reusable services and widgets.
- **Content & marketing** features such as news, landing pages, SEO metadata, and email campaign tooling with automated translations.
- **Content safety guardrails** with a centralized HTML sanitization service, reusable Blade renderer, and `php artisan maintenance:sanitize-html` command for retrofitting stored markup.
- **Operational dashboards** for activity logs, analytics, and background job health leveraging Laravel Horizon, Scout, and bespoke widgets.
- **Feature flag governance** with Filament listings that expose inactive and disabled toggles for quick rollout audits and remediation.
- **Multilingual experience** across storefront and admin via `spatie/laravel-translatable`, Volt-powered Livewire pages, and localized seed data.
- **Configurable system setting dependencies** with operator-specific value fields, translated labels, and duplication safeguards for precise feature toggles.

-### Latest updates
- Graceful filesystem shim now powers test backups, automatically running `backup:prepare`/`backup:verify` when fresh directories are created so artisan exit-code assertions no longer flake during metadata checks.
- Database index audit regression coverage spins up an isolated SQLite database file per run, avoiding collisions with the shared testing connection while still exercising duplicate detection and cleanup.
- Filament top navigation widget now derives navigation groups from the enum metadata with permission and role awareness, giving Livewire tests deterministic ordering and complete coverage of admin/public sections.
- Attribute validation rules now persist plain strings alongside array-based rule lists, the Filament Attribute editor hydrates
  those values without forcing JSON, and new regression coverage keeps both storage paths stable.
- Region-aware address tables and the dedicated `customers` dataset are back online for the SQLite harness, letting factories and
  analytics widgets build customer journeys without missing foreign keys during PHPUnit runs.
- SearchableInput payload macros are registered lazily with safer fallbacks, and the SQLite testing harness now spins up one
  database per parallel worker so hydrate/clear flows avoid TypeErrors and filesystem locks during `php artisan test --parallel`.
- System setting translation records can once again be soft deleted, restored, and replicated thanks to the relaxed locale index and leaner fillable list that align with the documented API expectations.
- Product review aggregates in the API now reuse eager-loaded counts and averages, eliminating redundant queries and
  keeping cached storefront metrics intact for the Product API regression suite.
- Campaign conversion analytics now keep their translation model, timestamps, and
  scope filters aligned so ROI/ROAS dashboards and PHPUnit coverage see the same
  completed conversions without fighting `is_active` guards.
- Brands directory now ships with a light-focused layout, shared card components, and complete EN/LT copy so visitors get a consistent multilingual experience.
- Localized slug routing and demo collection seeding were hardened, keeping home-page product links, category listings, and collection showcases working out of the box.
- Test harness now provisions a shared `database/testing.sqlite` datastore during `createApplication`, runs focused SQLite-only migrations (including Spatie permission and attribute pivots), and registers Filament SearchableInput payload macros so PHPUnit suites reuse the same schema while keeping component helpers v4-compatible.
- Filament dashboard access now defaults to permissive access when no
  abilities are configured and the inline sparkline widgets honour the
  nullable model contract, keeping admin unit tests green.
- Campaign customer segment queries now expose dedicated scopes for type,
  campaign, customer group, and activity state, letting unit tests fetch
  inactive records while admin tooling keeps expressive filters without
  fighting a blanket global scope.
- Campaign conversion analytics now bypass the generic ActiveScope filter, keeping
  completed conversion records visible so the type/status/device scopes used in
  the unit suite and marketing dashboards return accurate datasets again.
- Document template management now uses Filament v4 schema layouts, enum-backed factories, and plain-text content persistence so CRUD flows, filters, and related document lookups all succeed in automated tests and the admin panel.
- Stabilized the NewsCategory regression suite by restoring the RefreshDatabase migration flag after the toggleable table Pest harness runs and making news category factories default to visible records so relationship tests load scoped children reliably.
- Attribute value management in the Filament admin now bypasses storefront
  scopes, ensuring inactive or disabled options stay editable and helper
  actions (activate, duplicate, default toggles) behave consistently during
  regression tests and live admin sessions.
- Collection management resource navigation and translations were
  synchronized with the Filament v4 schema, keeping the admin sidebar readable
  in EN/LT and aligning the comprehensive resource regression suite with the
  model's fillable contract.
- Cart lifecycle regression tests now provision a lightweight `cart_items` schema inside the suite, keeping checkout cleanup coverage reliable without invoking the full migration set.
- Catalog OpenAPI contract now documents the lean product meta payload and nullable image thumbnails, ensuring schema validation mirrors real API responses.
- Campaign click factories now guard optional relationships and lean on the dedicated SQLite test database configuration, eliminating the missing-table errors that previously interrupted the API listing regression suite.
- Restored the missing `App\\Exceptions\\Handler` so Laravel can bootstrap without the fatal `Whoops\\Run::handleShutdown()` error that previously surfaced on every web request and artisan command.
- Test runs now provision an on-disk SQLite database and guard customer group metadata seeding, eliminating the intermittent `no such table: users` failure encountered by the user attribution observer suite.
- Shipping options now expose explicit zone relationships and fillable references, letting orders and delivery zones surface
  carrier data consistently during automated regression runs.
- HTML sanitization now strips entire `<script>`, `<style>`, and `<template>` elements instead of unwrapping them, ensuring
  malicious payloads do not leak into storefront or admin renders while preserving allowed markup for editors.
- Search API now detects suspicious injection fragments, skips database execution, and keeps exact-title matches at the top of result sets so precise catalogue lookups stay reliable while hostile payloads return empty responses.
- Customer and product inline sparklines now reuse the cached analytics series and publish stable dataset checksums, keeping Filament tables and unit tests aligned on the same Chart.js payloads.
- Search endpoints now respect mixed-case `types[]` filters by normalizing them server-side, preventing fallback to all buckets when storefront clients request specific result categories.
- Company model unit tests now bootstrap the SQLite `companies` table and defer the active scope until migrations complete, eliminating missing-table crashes during `php artisan test` runs.
- Storefront autocomplete now trims and caches queries, reuses injected services for faster bucket lookups, and delivers safe highlight markup so Live Search suggestions no longer show raw `<mark>` tags.
- Analytics event tracking now skips restrictive user scopes during console execution, tolerates missing request data, and reports float-safe revenue totals so dashboards and regression suites stay in sync.
- API rate limiting and authorization helpers now fall back to raw configuration files when container bindings are unavailable, allowing console diagnostics and unit tests to execute without fatal bindings.
- Localized search results now ship with a guided hero, contextual metrics, and improved empty states so catalog lookups (like Makita) surface faster insights and next steps.
- PHPUnit now bootstraps a persistent SQLite database and reroutes Telescope migrations to SQLite, keeping `php artisan test` green without provisioning a MySQL service.
- Corrected the custom Filament edit profile form to import `Filament\\Schemas\\Schema`, preventing namespace resolution fatals during profile updates and automated test runs.
- Discount Redemption admin navigation now lives in the Marketing cluster with a warning badge and Filament v4 badge styling, and its Pest harness boots a lightweight `HasTable` stub so table schemas construct cleanly during unit tests.
- Pest test helpers now guard the `login()`, `get()`, and `post()` helpers with existence checks, preventing redeclaration fatals during repeated `php artisan test` bootstrap cycles.
- PHPUnit now provisions a persistent `database/testing.sqlite` database and points Telescope/Activity Log to the SQLite connection before migrations run, keeping catalog integrity checks from tripping missing table errors during local CI loops.
- Order seeding now uses the expanded `orders.status` enum (`confirmed`, `completed`, and return-friendly values) so `php artisan migrate:fresh --seed` no longer trips MySQL truncation warnings when loading the demo store checkout history.
- Hardened the `2025_02_15_120000_add_created_at_indexes` migration with case-insensitive index detection and information schema fallbacks, eliminating duplicate key errors during repeated deploys or fresh seeds.
- Expanded the currency schema and demo country seeder so multilingual fields, activation flags, and translation records stay in sync, keeping `php artisan migrate:fresh --seed` reliable on both SQLite and MySQL setups.
- Added an initial `customer_groups` table migration so downstream enhancements (missing fields, soft deletes, translations) run without errors during `php artisan migrate:fresh --seed` on clean environments.
- Stock reservation migrations now stage foreign keys after their parent tables exist, preventing `php artisan migrate:fresh --seed` from failing on pristine databases while keeping cascade rules in place for live systems.
- Discount schema rebuild scripts now re-enable foreign key checks before recreating tables and only disable them while copying legacy rows, eliminating the MySQL system-table constraint error triggered during `php artisan migrate:fresh --seed`.
- Filament admin navigation now standardises every icon and group declaration on the BackedEnum/UnitEnum union types mandated by v4, eliminating the PHP 8.3 fatals that previously surfaced during `composer install`.
- Shipping option models now expose their zone relationship, eligibility guards, and scoped queries so fulfilment rules stay enforceable across factories, admin listings, and automated tests.
- Logged the Oct 21–22, 2025 PR triage outcome directly in `docs/analysis/CURRENT_SYSTEM_STATUS.md`, highlighting which Husky and Filament fixes are ready to merge, which legacy branches to close as superseded, and which submissions still need action so maintainers can prioritise reviews without re-scraping GitHub.
- Curated a high-level repository analysis that catalogues the 24 open pull requests, highlighting the repeated Filament Schema API migrations, Husky bootstrap shim restorations, and the layered rate-limiting proposal so maintainers can prioritise reviews without manually expanding each PR.
- Documented the open proposal from PR #289 to introduce layered API rate limiting buckets for read, write, notification, and autocomplete endpoints alongside enhanced throttling logs with correlation identifiers, so reviewers can quickly trace the outstanding security hardening work.
- Filament admin resources, relation managers, and bespoke pages now return `Filament\\Schemas\\Schema` instances with documented icon docblocks, aligning every form/table/infolist signature to the v4 API so upstream navigation traits continue to resolve enum-aware metadata without collisions.
- Product API endpoints now resolve via dedicated application use cases, an Eloquent-backed repository, and a presenter that preserves the public contract while filtering non-displayable catalogue entries.
- Activity Log admin navigation now declares its icon using the BackedEnum-aware union type expected by Filament v4, eliminating the fatal error encountered during resource bootstrapping.
- Notification administration now proxies navigation metadata through the shared Nav helper, the helper guards against HasNav recursion, and the Address resource documents its `Schema::components([...])` pipeline so Filament v4 reviewers understand the form container wiring.
- Menu Item resource navigation icons rely on the shared docblock convention with documented schema/table delegators, keeping Filament v4 reviewers aligned on our configurator pattern.
- Wishlist Item admin navigation now uses the Filament-standard static icon property with a documented sidebar sort note, keeping customer tooling metadata consistent across the admin.
- Diagnostics coverage moved from bespoke artisan commands to dedicated PHPUnit suites with a minimum coverage extension and Paratest-ready tooling, making the quality gate observable directly from standard test runs.
- Security middleware now shares request-scoped CSP nonces with Livewire and Vite, tightens HSTS/permissions policy headers, and updates inline Blade assets to honour nonce-based CSP directives end-to-end.
- API error handling now exposes a shared `error.rate_limited` problem code for HTTP 429 responses, giving integrators a stable throttle signal documented in [docs/contracts/ERRORS.md](docs/contracts/ERRORS.md).
- Validation problem responses now backfill a fallback English reason while preserving the localized violation list so RFC&nbsp;7807 consumers receive consistent messaging even when validation runs before locale negotiation completes.
- Forbidden API responses raised by Symfony's `AccessDeniedHttpException` now retain their explicit denial reason in `error.context.reason`, aligning the payload with Laravel's authorization exception handling.
- Test bootstrap now registers both `lang/` and `resources/lang/` JSON translation directories so admin navigation labels render localized text during regression tests.
- Cache tag conflicts from PR #120 are resolved: dashboard widgets, storefront navigation, and product shelves now share locale-aware tags with automatic invalidation hooks so cached payloads refresh the moment catalogue records change, and new feature tests guard the behaviour.
- A new cache invalidation service now coordinates dashboard and storefront tag flushing, and the cart/checkout flows gained JSON regression tests to keep the customer journey stable across releases.
- The Filament schema upgrade tooling now normalizes navigation icon docblocks and reruns cleanly across every resource, keeping the v4 Schema migration safe to execute repeatedly without manual cleanup.
- Reusable HTML sanitization now protects product and legal descriptions end-to-end, complete with a maintenance command and storefront Blade helper for rendering cleaned markup.
- User Product Interaction analytics tables now present rating badges and interaction filters with Filament v4-aligned formatting, keeping admin seeding from tripping PHP concatenation notices observed in upstream PR #1097 testing.
- Developer tooling regained the full Husky bootstrap shim with its executable bit restored, ensuring local Git hooks run automatically after installs while still reminding contributors about the upcoming v10 script changes.
- Shipping option management screens now present accurate delivery windows even when the minimum day value is zero or one side of the range is missing, giving merchandisers clearer expectations when auditing carriers.
- Filament admin navigation icons once again rely on docblock-based overrides, preventing BackedEnum collisions, while variant stock history badges group destructive events under the shared danger palette and the `data:import` command advertises its purpose directly in Artisan listings.

### Latest updates
- Tightened the Filament price list discount filter so that only products with a genuine markdown (compare price greater than the selling price) appear when toggled.
- Added a feature test that exercises the discount filter to ensure future changes keep the behaviour intact.

## Documentation

- Start with the curated [documentation index](docs/INDEX.md) for the setup → deploy → data model → admin guide → troubleshooting path.
- Follow the living [documentation style guide](docs/STYLE_GUIDE.md) when adding reports, runbooks, or contracts so navigation stays predictable.
- Browse the rest of the knowledge base directly in [docs/](docs/), especially the dedicated [analysis](docs/analysis), [runbooks](docs/runbooks), and [contracts](docs/contracts) directories introduced during the consolidation effort.

## Requirements

- PHP 8.2+ with `ext-sqlite3`, `ext-fileinfo`, and `ext-gd`
- Composer 2.6+
- Node.js 20+ with npm 10+
- SQLite (default local database) or MySQL/PostgreSQL if you prefer
- Make (optional but recommended for the helper targets below)

## Quick start (3 commands)

1. **Bootstrap dependencies and environment**
    ```bash
    make setup
    ```
    Installs Composer and npm dependencies, copies `.env`, prepares the SQLite database, and links storage.
2. **Reset the database and seed demo data**

    ```bash
    composer seed:fresh
    ```

    Runs `php artisan migrate:fresh --seed` so you get the admin user (`admin@statybaecommerse.prus.dev` / `admin123`) and sample content in one shot.

    Seeder profiles are centralized in `config/seeds.php` so you can control how much demo data is loaded:
    - `php artisan db:seed --profile=minimal` seeds only the essentials for admin access and catalog metadata.
    - `php artisan db:seed --profile=full` (default) includes all demo storefront content on top of the minimal set.

    Set `DB_SEED_PROFILE=minimal` in `.env` to change the default profile used by seeding commands.

3. **Serve the application**
    ```bash
    composer serve
    ```
    Visit the storefront at http://127.0.0.1:8000/ or the admin panel at http://127.0.0.1:8000/admin.

Need background workers, logs, and Vite in one go? Use the existing dev loop:

```bash
composer run dev
```

### Testing & contract validation
- Run `php artisan test --filter=ContractValidationTest` to verify the public product, category, brand, order, and user contracts. The bootstrap now creates `database/testing.sqlite` automatically so migrations and model factories align with the published schemas even on clean machines.

## Latest maintenance

- 2025-10-24: Normalised every Filament navigation icon and group declaration to the BackedEnum/UnitEnum union types expected by v4 so CLI installs and admin boots succeed on PHP 8.3.

- 2025-10-24: Reinstated the Husky bootstrap shim file contents and permissions so Git hooks execute using the local toolchain again while continuing to surface the upstream v10 deprecation warning banner for contributor awareness.
- 2025-10-24: Hardened content security policies with request-scoped nonces, refreshed inline Blade assets to inject the helper automatically, and expanded rate limiting configuration so API throttles can stack per-minute and per-hour limits.
- 2025-10-25: Re-sequenced the discount rebuild migration to stage renames before table creation and only suppress constraints during data copy, resolving the MySQL `discount_codes_created_by_foreign` failure encountered in `php artisan migrate:fresh --seed`.
- 2025-10-25: Deferred the rebuilt discount code and redemption user foreign keys until after compatibility checks so MySQL restores from mixed storage engines no longer trigger the `discount_codes_created_by_foreign` system-table error.

- 2025-10-23: Wired the cache invalidation service into global model events, updated navigation caches to use shared tag helpers, and expanded regression coverage so dashboard stats and storefront widgets refresh automatically after catalogue edits.

- 2025-10-22: Extended the Filament schema upgrade script to normalize navigation icon docblocks automatically and refreshed every resource/page/widget to the standardized format.
- 2025-10-21: Tightened the User Product Interaction Filament resource to return concrete `Form` and `Table` instances so Filament v4 boots cleanly when analytics interactions are seeded.
- 2025-10-21: Polished rating badge formatting and interaction filters in the same resource to eliminate concatenation warnings during analytics QA for PR #1097.
- 2025-10-21: Reinstated docblock navigation icon overrides, consolidated variant stock danger badges, and documented the `data:import` signature/description so the fixes from PR #1098 remain stable across admin tooling and CLI discovery.

## Architecture cheatsheet

| Area                  | Location(s)                                                                                         | Notes                                                                                                                                                                                                       |
| --------------------- | --------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| HTTP entrypoints      | `routes/web.php`, `routes/api.php`, `routes/admin.php`                                              | Inertia/Volt storefront routes live in `web.php`; Filament registers admin routes in `AdminPanelProvider`.                                                                                                  |
| Filament admin        | `app/Filament/**`                                                                                   | Resources, widgets, custom pages, and global actions follow Filament v4 conventions outlined in [docs/analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md](docs/analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md). |
| Domain models & data  | `app/Models`, `app/Data`, `database/migrations`, `database/factories`, `database/seeders`           | Data objects use `spatie/laravel-data`; factories and multilingual seeders ensure parity between Lithuanian and English content.                                                                            |
| Business services     | `app/Services`, `app/Actions`, `app/Support`                                                        | Encapsulated workflows (pricing, availability, marketing, search) with helper traits for caching and localization.                                                                                          |
| Background processing | `app/Jobs`, `app/Listeners`, `app/Notifications`, `app/Console`                                     | Horizon manages queues; recurring tasks registered through `Console/Kernel.php`.                                                                                                                            |
| Frontend assets       | `resources/views`, `resources/js`, `resources/css`, `tailwind.config.js`, `vite.config.js`          | Uses Tailwind v4 + Vite; Livewire Volt pages bridge server-driven UI to the storefront.                                                                                                                     |
| Quality & automation  | `Makefile`, `composer.json` scripts, `package.json` scripts, `scripts/*.mjs`, `autofix-realtime.sh` | Make targets wrap Composer/NPM scripts; MCP tooling (`mcp/filament-docs-server.js`) serves Filament component docs locally.                                                                                 |

## Data and integration dependencies

- **Caching & queues**: Redis (or Predis) backing Horizon for real-time metrics and queue execution.
- **Search**: Toggle between database and Laravel Scout engines with `SEARCH_DRIVER`; keep `SCOUT_ENABLED=false` locally for SQL fallback, or enable Scout for Algolia/Meilisearch and rebuild indexes via `php artisan search:index`.
- **Media**: `spatie/laravel-medialibrary` manages product imagery, generated conversions, and downloads.
- **PDF & exports**: `barryvdh/laravel-dompdf` and `pxlrbt/filament-excel` power report exports from the admin panel.

## One-liners for build, quality, and tests

| Task                      | Command                       |
| ------------------------- | ----------------------------- |
| Run feature & unit tests  | `make test`                   |
| Static analysis           | `make analyse`                |
| PHP formatting            | `make format`                 |
| Build production assets   | `make build`                  |
| Generate coverage locally | `php artisan test --coverage` |

## Latest maintenance notes
- Re-applied the documented docblock pattern for Filament navigation icons to stay aligned with v4 schema upgrades after the #533 regression.
- Consolidated variant stock history danger badge mappings so both damage and theft events share the intended styling.
- Clarified the `data:import` command metadata with typed properties to improve Artisan list readability.

## Composer script quick reference

| Script                | What it does                                                                                              |
| --------------------- | --------------------------------------------------------------------------------------------------------- |
| `composer ci`         | Runs PHPStan (`phpstan analyse`) then executes the CI-friendly PHPUnit suite (`phpunit --log-junit ...`). |
| `composer analyse`    | Alias of `composer analyze` for PHPStan static analysis.                                                  |
| `composer seed:fresh` | Proxies `php artisan migrate:fresh --seed --ansi` to rebuild the database with demo data.                 |
| `composer build`      | Calls `php artisan optimize --ansi` before `npm run build` to prep caches and assets.                     |
| `composer serve`      | Uses `php artisan serve --ansi` for a local HTTP server.                                                  |

## Configuration notes

- Environment defaults live in `.env.example`; copy it to `.env` to tweak database/queue/mail settings.
- SQLite is enabled by default for fast onboarding—switch `DB_CONNECTION` in `.env` if you need MySQL/PostgreSQL.
- PHPUnit test runs now target the shared `database/testing.sqlite` file by default for persistent schema reuse; override `DB_DATABASE` locally if you prefer transient in-memory databases.
- Storage symlink (`public/storage`) is created by `make setup`; re-run `php artisan storage:link` if you remove it.
- Horizon, Scout, and media-processing queues expect Redis; fall back to the sync driver for local smoke testing by setting `QUEUE_CONNECTION=sync`.
- Rebuild search indexes for Scout with `php artisan search:index --fresh` or schedule zero-downtime refreshes through `php artisan search:index:rebuild`.
- Frontend assets rely on modern Node (20+) with native ESM; ensure `npm install` runs before invoking Vite or Playwright scripts.

## Further reading

- Start with [docs/INDEX.md](docs/INDEX.md) for a curated guide to deployment runbooks, feature deep-dives, and historical archives.
- Need domain-level context? Check [docs/analysis/COMPANY_RESOURCE_ANALYSIS.md](docs/analysis/COMPANY_RESOURCE_ANALYSIS.md) and the project summaries collected under [docs/analysis/](docs/analysis/).
- Want a system tour? Review [docs/ARCHITECTURE_OVERVIEW.md](docs/ARCHITECTURE_OVERVIEW.md) for component breakdowns and integration diagrams, then keep the runbooks in [docs/runbooks/](docs/runbooks/) close for operational workflows.

Happy building!

---

## Platform Features & Highlights (formerly `FEATURES.md`)

This snapshot complements the changelog by listing functional capabilities that ship with the storefront and admin panel.

## Core Commerce Platform

- Laravel 12 + Filament v4 admin with multilingual product, pricing, discount, and order management flows.
- Customer loyalty, referral tracking, and recommendation engines with configurable targeting rules.
- Automated media processing, queue orchestration, and analytics dashboards for store operators.

## Storefront Experience

- Livewire-powered storefront pages with localisation, SEO metadata, and responsive catalogue browsing.
- Checkout, cart persistence, and account management journeys wired to the same aggregates used in the admin UI.

## Operational Tooling

- Queue, cache, and deployment runbooks collected under [`docs/runbooks/`](docs/runbooks/) for production readiness.
- API contracts, payload samples, and integration notes organised in [`docs/contracts/`](docs/contracts/).
- Analytical reports, project retrospectives, and rollout summaries consolidated inside [`docs/analysis/`](docs/analysis/).

## Latest Update
- Graceful filesystem shim now sits behind the global `files` binding, ensuring automated backup prepare/verify commands run when tests create fresh directories so scheduled backup coverage stops flaking.
- Database index audit command suite runs against a dedicated SQLite database file, keeping duplicate-index detection isolated while preserving the cleanup assertions that power the console workflow.
- Filament top navigation widget respects admin roles, permission requirements, and enum-defined ordering, aligning Livewire regression coverage with the expected navigation tree.
- Brands page now features a light-themed layout, shared card components, and refreshed translations so the partner directory feels consistent across locales.
- Product API endpoints now honour eager-loaded review aggregates so cached rating/count metrics stay in sync while
  trimming redundant queries from feature and regression suites.
- Region-to-city lookups and the dedicated customers table are restored for the SQLite harness, ensuring analytics sparklines, customer factories, and Filament resources can attach geographic metadata without migration errors.
- Localized product/category routing plus collection seeding were hardened, ensuring homepage product links, category landing pages, and collection showcases load without 404s or empty states.
- PHPUnit harness now boots a shared `database/testing.sqlite` schema (including Spatie permission tables and variant attribute pivots) and registers Filament SearchableInput payload macros so admin feature suites stay v4-compatible while reusing deterministic migrations.
- Parallel test execution now provisions per-worker SQLite databases using Laravel's parallel token, and the SearchableInput payload macros register on demand so hydrate/sync helpers avoid TypeErrors even when the provider layer has not initialised them yet.
- Filament dashboard access now defaults to an open posture when no
  permissions are configured and inline sparkline widgets comply with the
  nullable model contract, preventing dashboard and widget regressions.
- Currency resource workflows now expose inactive rows, accept longer ISO identifiers, and reuse the updated amount formatter so merchandising teams can audit every rate directly from Filament.
- Campaign customer segment management now relies on explicit query scopes for
  activity state, campaign, customer group, and segment type so analytics and
  admin listings can intentionally include inactive records without removing
  visibility controls from the model entirely.
- Campaign conversion queries now rely solely on the status-aware scope while
  exposing their translation model and defaulting fresh `converted_at` values, so
  completed, pending, and other lifecycle records remain accessible to analytics
  tooling without fighting an `is_active` filter that the table never exposed.
- Order analytics scopes now explicitly target the standalone created-at index,
  the orders migration seeds that index for clean installs, and diagnostics
  seeders once again persist processing orders after refining the shared active
  scope defaults.
- Restored the default RefreshDatabase migration flow after the toggleable table Pest suite and ensured the news category factory seeds visible records so unit coverage can assert parent/child/category pivots without global scope interference.
- Catalog contract docs now capture the streamlined product meta payload and nullable media thumbnails so integrators see the same shape published by the API presenter.
- API validation errors now bundle localized violation lists with a fallback English reason so partner integrations can act on stable messaging even when the initial validation precedes locale negotiation.
- Fallback validator execution now explicitly aligns the translator with the configured fallback locale, keeping English problem reasons free of untranslated placeholders and preserving consistent messaging for integrators.
- Access denied problem responses produced by Symfony's HTTP layer now echo the denial reason inside `error.context.reason`, matching Laravel's authorization payloads and keeping client handlers consistent.
- Test infrastructure now provisions an on-disk SQLite database and conditionally seeds customer group metadata, preventing the observer test suite from failing with missing table or column errors.
- Campaign click analytics respect deterministic UTC timestamps, the authenticated user endpoint keeps Sanctum ability messaging within the RFC 7807 schema, and factory-generated categories use collision-free slugs so SQLite feature suites exercise the same flows as MySQL.
- System setting translation workflows regained soft delete, restore, and replication support thanks to a relaxed locale index and streamlined fillable contract that better reflects the documented API surface.
- API search now short-circuits suspicious payloads and boosts exact-title matches so catalogue lookups stay precise while SQL injection attempts return empty responses.
- Search experiences normalise mixed-case `types[]` filters so targeted product/category/brand lookups keep the requested scope even when storefront clients send capitalised identifiers.
- Attribute management in the Filament admin preserves plain string validation rules, converts stored arrays into readable comma-separated chips, and carries new regression tests that guarantee both storage paths round-trip without JSON artefacts.
- The custom Edit Profile page now imports `Filament\\Schemas\\Schema`, keeping Filament authentication tooling aligned with v4 expectations and eliminating namespace-related fatal errors during automated runs.
- Discount Redemption admin tooling now groups under Marketing with a warning navigation badge, uses Filament v4 badge styling for status indicators, and ships with a HasTable-aware Pest harness so table schemas build successfully during tests.
- Pest-powered test helpers now wrap the `login()` helper in a function-existence guard so repeated bootstrap cycles during `php artisan test` runs avoid fatal redeclaration errors.
- Order lifecycle tooling now recognises the expanded `orders.status` enum (including confirmed, completed, and returned flows), keeping admin filters and demo seeds consistent without MySQL truncation warnings during fresh installs.
- Introduced a baseline `customer_groups` table so every subsequent enhancement (extra fields, translations, soft deletes) can execute successfully during fresh database provisions and automated refresh cycles.
- Strengthened database bootstrap flows by guarding created_at index migrations against duplicates and aligning currency/country seed data with multilingual schemas, keeping one-command installs reliable.
- Stock reservation schema guardrails now delay foreign key enforcement until product and variant inventory tables are present, keeping fresh database bootstraps reliable without dropping cascade behaviour.
- Discount code rebuild tooling now sequences table renames, foreign key toggles, and legacy data copy so MySQL-based installs no longer hit the system-table constraint error when refreshing the database.
- Discount schema rebuilds now attach user foreign keys after verifying the `users` table engine/collation, keeping production MySQL dumps with legacy MyISAM tables from blocking migrations.
- Added a repository analysis snapshot that summarises the 24 open pull requests into themes—Filament Schema migrations, Husky bootstrap shim restorations, and layered rate limiting—so product and engineering stakeholders can digest the queue at a glance.
- Tracking the open security enhancement proposal from PR #289 that introduces layered API rate limits per user and IP, distinct buckets for read/write/notification/autocomplete flows, and correlation-aware throttling logs so operators can assess the pending protection improvements.
- Filament admin resources, relation managers, widgets, and standalone pages now follow the Schema-based API with normalized navigation icon docblocks, eliminating BackedEnum collisions while documenting how each builder composes the new schema pipeline.
- Navigation icons and groups across every Filament page, resource, widget, and relation manager now rely on the BackedEnum/UnitEnum union types mandated by v4 so PHP 8.3 environments boot without property type fatals.
- Product API contract delivery now runs through dedicated application-layer use cases, a presenter, and an Eloquent repository, ensuring storefront consumers receive filtered, displayable catalogue data without breaking schema guarantees.
- Notification administration delegates navigation metadata to the Nav registry with recursion safeguards, and address management documents its Filament `Schema::components([...])` pipeline for reviewers aligning with v4 expectations.
- Menu Item administration adopts the shared navigation icon docblock and documents the reusable schema/table configurators so Filament v4 resource reviews stay consistent.
- Wishlist Item admin navigation metadata now uses the Filament-standard static icon property with a documented sidebar sort value, keeping customer tooling grouped consistently.
- Diagnostics coverage is now enforced through PHPUnit suites with a configurable minimum coverage extension and Paratest-ready composer scripts, removing the need for bespoke artisan commands when validating seeders and resource metadata.
- Developer tooling once again ships with the Husky bootstrap shim under version control, keeping Git hooks active after fresh clones while flagging the upcoming v10 script migration path for contributors.
- Middleware-delivered CSP nonces now flow through Livewire, Vite, and inline Blade assets alongside stricter HSTS and permissions policy defaults, raising the platform's baseline security posture.
- API error responses now surface an explicit `error.rate_limited` code for HTTP 429 cases so partner integrations can detect throttling conditions without parsing status text.
- Test infrastructure now reloads JSON translation directories so Filament commerce navigation continues to present localized labels during automated checks.
- Resolved the open cache invalidation conflicts by tagging navigation, product, and dashboard caches with the shared helper, wiring the invalidation service into global model events, and adding regression tests that prove storefront widgets and stats refresh immediately after catalogue edits.

---

## Additional Feature Deep Dive (formerly `features.md`)

## Fulfilment & Logistics

- Orders and zones now rely on first-class shipping option relationships, so factories and regression tests can link carriers
  without hand-setting foreign keys.
- Stock reservation migrations now create their tables before wiring foreign keys, so full database refreshes finish cleanly while live environments keep cascading deletes for products and variant inventories.
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.
- Shipping option entities once again declare their zone relationship, eligibility checks, and filter scopes, ensuring fulfilment automation and tests can target the correct carrier records without custom query shims.

## Pricing & Currency
- Filament currency administration now accepts extended ISO identifiers, surfaces inactive entries for auditing, and respects per-currency separators when rendering formatted amounts.

## Data integrity & seeding
- Database index audit regression coverage now provisions a dedicated SQLite database file and cleans it up after assertions, so duplicate-index detection runs in isolation without colliding with the shared testing connection.
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
- User profile data contract exports now emit UTC timestamps and validate CSV/JSON payloads defensively, keeping the round-trip import/export workflow in sync with the documented fixtures.

## Discounts & promotions
- Discount redemption listings now leverage a dedicated status scope branch so pending, redeemed, expired, and cancelled entries remain visible for Filament CRUD workflows, regression tests, and seeded demo data.
- Coupon migrations now provision maximum discount caps, per-user usage limits, and product/category scoping columns so factories, admin forms, and API tests share the same schema snapshot during refreshes.
- Coupon application responses now round computed totals instead of calling `Number::parseFloat` on floats, keeping the discount API stable across PHP 8.3 test runs.

## Storefront discovery
- Untitled UI icon components and Filament grid fallbacks now ship in-repo, fixing Blade caching failures and preventing missing icon placeholders across brand, category, and product listings.
- Brands directory was redesigned with a light-themed layout, shared card components, and refreshed translations for English and Lithuanian so partner browsing feels polished across locales.
- Search endpoint hardening now rejects suspicious SQL fragments and adds an explicit exact-match boost so precise catalogue queries surface first and malicious payloads return empty buckets.
- Search type filters now normalise mixed-case identifiers from clients, ensuring storefront queries stay restricted to the requested product, category, or brand buckets instead of ballooning to every result group.
- Catalogue search now rebuilds product metrics from related tables, honours the `product_categories` pivot, and tolerates Redis being unavailable so the API behaves consistently across MySQL and SQLite deployments.

## API contracts
- Partner API middleware now delivers the documented JSON error envelopes for missing credentials, insufficient scopes, and rate limit violations so partner integrations and automated tests can rely on stable status codes.
- OpenAPI documentation now mirrors the lean product meta payload and nullable media thumbnails emitted by the presenter, keeping schema validators and client SDKs in sync with production responses.
- Product endpoints now reuse eager-loaded review counts and averages, keeping API consumers aligned with cached
  storefront metrics while trimming duplicate queries from feature coverage.

## Admin panel resilience
- Filament top navigation widget surfaces the enum-driven metadata with admin/role checks and deterministic ordering, letting Livewire tests confirm visibility rules without missing groups or inconsistent priorities.
- Attribute administration keeps validation rule strings verbatim, surfaces array-based rules as comma-separated chips, and pairs with regression tests that prove both paths round-trip correctly through Filament.
- Filament dashboard tests spin up a minimal widget stack with a temporary Vite manifest and heroicon fallback while keeping full resource/page discovery active, and a dedicated admin authenticate middleware redirects guests to the Filament login screen, keeping feature coverage aligned with browser flows.
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
- Variant Attribute Value listings extend that behaviour to the table filters,
  removing variant and attribute storefront scopes so QA can reliably surface
  inactive fixtures when narrowing results by attribute or variant.
- Test bootstrapping now adds both JSON translation directories so Filament's commerce navigation label renders localized copy during PHPUnit runs.
- The PHPUnit harness now seeds a dedicated SQLite testing database and directs Telescope/Activity Log to that connection before migrations execute, so catalog integrity and other schema-heavy suites run without missing table errors.
- Filament navigation icons once again use docblock overrides so enum-aware navigation metadata loads without typed property collisions introduced in PR #1098.
- Analytics dashboards now declare navigation metadata via the shared docblock convention and keep the resource form signature aligned with Filament v4 expectations, preventing BackedEnum type collisions during admin boot.
- Campaign conversion metrics now bypass the generic ActiveScope, expose their translation model, and seed fresh conversion timestamps so analytics widgets and unit tests agree on which records qualify for ROI and ROAS summaries.
- Variant stock history tables consolidate destructive change reasons under the `danger` badge, keeping badge colors predictable for both `damage` and `theft` events.
- User Product Interaction analytics pages restore Filament v4-friendly spacing for interaction filters and rating badges, silencing the concatenation notices flagged while QAing PR #1097.

## Storefront experience
- Password reset Livewire page now reuses shared button components and maintains the CAPTCHA token via a hidden field so rate-limited recovery flows load without missing component errors.
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
- Global filesystem binding now resolves to the GracefulFilesystem shim, automatically triggering `backup:prepare`/`backup:verify` during test runs when fresh backup directories appear so artisan exit-code checks stay deterministic.
- `scripts/upgrade_filament_schema.php` now updates navigation icon docblocks automatically while refactoring `form`, `infolist`, and `table` signatures, making repeated schema migrations safe for the entire Filament tree.
- The `data:import` Artisan command now documents its signature and description directly on the command class, improving discoverability via `php artisan list`.
- Legacy diagnostics artisan commands were retired in favour of PHPUnit suites guarded by a configurable coverage extension and Paratest-aware composer scripts, making quality checks part of every test run.

## API experience
- Notification API mutations now drop redundant `message` wrappers and stringify throttle headers so clients consistently
  receive 429 problem payloads without triggering Symfony header type errors.
- Product search, catalogue, and detail endpoints resolve via dedicated application use cases, an Eloquent-backed repository, and a presenter that preserves the public contract while filtering hidden or malformed catalogue entries.
- Problem+JSON responses now include the shared `error.rate_limited` code for HTTP 429 throttling scenarios, helping integrators react uniformly when the throttle middleware triggers.
- Validation problem responses now deliver localized violation arrays plus a fallback English reason so clients can show consistent messaging while still exposing locale-specific details.
- Fallback validator replay now forces the translator onto the configured fallback locale, preventing placeholder-filled summaries and reinforcing the guarantee that English problem reasons stay readable for integrators.
- Access denied HTTP exceptions now keep their explicit denial reason inside `error.context.reason`, aligning Symfony-generated responses with Laravel's authorization handler contract.

## Documentation consolidation

- Documentation now lives in dedicated `docs/analysis/`, `docs/runbooks/`, and `docs/contracts/` directories, with a new [style guide](docs/STYLE_GUIDE.md) and CI guard ensuring Markdown stays reviewable.

## Project governance

- Maintainer playbooks now capture the Oct 21–22, 2025 PR triage in `docs/analysis/CURRENT_SYSTEM_STATUS.md`, calling out immediate merges, superseded branches, and fix-required submissions so review queues stay actionable without reprocessing GitHub filters.

## Reference

- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
- Husky pre-commit hook now re-stages Pint output and streams staged PHP paths through `xargs` for PHPStan, so contributors no longer need to bypass the hook on environments without `--paths-file` support.
- Filament analytics utilities reference the updated User Product Interaction resource so schema contract mismatches no longer block admin boot sequences.

---

## Changelog (formerly `CHANGELOG.md`)

All notable changes to this project will be documented in this file.

The format is based on [Conventional Commits](https://www.conventionalcommits.org) and is automatically generated by [release-please](https://github.com/googleapis/release-please).

## [Unreleased]

### Bug Fixes
* Stabilized backup lifecycle commands in tests by routing filesystem calls through a new graceful shim that auto-runs `backup:prepare`/`backup:verify` when directories are missing, preventing race conditions in backup metadata assertions.
* Tightened the Filament top navigation widget to honour admin roles, permission-gated sections, and deterministic ordering so regression coverage sees every navigation group the widget is expected to expose.
* Re-enabled flexible system setting translations by replacing the locale uniqueness constraint with an index, restoring soft delete support, and trimming the fillable contract so replication and counting scenarios match the documented API.
* Preserved Attribute validation rule strings while still decoding JSON arrays, refreshed the Filament form so arrays render as comma-separated chips, and added regression coverage for both storage paths.
* Reintroduced the `regions` schema with defensive guards and rebuilt the `customers`/`orders` relationship so SQLite-backed factories and analytics widgets can create location-aware records without missing column errors during tests.
* Resolved localized product and category routing by honouring translated slugs during route model binding and updating storefront links so product detail pages load reliably from the home feed and other localized listings.
* Restored the dashboard permission guard to default to open access when no abilities are configured and aligned inline sparkline widgets with Filament's nullable model contract, clearing the latest unit test regressions around navigation metadata and dataset checksums.
* Replaced the CampaignCustomerSegment global ActiveScope with targeted query helpers so unit tests can fetch inactive records while dashboards retain expressive filters for campaign, type, and group segmentation.
* Removed the generic ActiveScope from campaign conversion analytics so status-
  filtered scopes (campaign, type, device, medium, etc.) once again return
  completed records in unit tests and dashboards instead of being filtered out
  by a non-existent `is_active` flag.
- Harmonised the campaign conversion translation model wiring, defaulted
  `converted_at` during factory creation, and kept the SQLite-friendly scopes in
  sync so ROI/ROAS dashboards and unit tests stop dropping recent conversions.
* Stabilized Filament dashboard feature coverage by loading a curated widget set in tests, falling back to the admin login route when guests hit `/admin`, and providing a safe heroicon fallback so missing assets no longer crash the page render.
* Kept Filament resource and page discovery active during the test suite while layering the deterministic dashboard widgets and explicit route registration, ensuring comprehensive admin integration coverage stays green alongside the focused dashboard assertions.
* Normalized user profile data contract exports to emit UTC timestamps and hardened JSON/CSV parsing so the round-trip fixtures match the expectations codified in `UserProfilesDataTransferTest`.
* Normalized API validation problem responses to always include a fallback English reason alongside the localized message list so integrators receive consistent messaging even when the initial validation ran before locale negotiation completed.
* Ensured forbidden problem responses raised through `AccessDeniedHttpException` retain the explicit denial reason in the error context, mirroring the structure used for authorization exceptions and keeping client-side handlers uniform.
* Added a dedicated discount redemption branch in the status scope so pending, redeemed, expired, and cancelled records surface in Filament listings and seeders without being filtered away by unrelated defaults.

### Enhancements

- Refreshed the public brands directory with a brighter layout, shared card components, and localized copy so the partner catalogue feels lighter and consistent across languages.
- Optimized the storefront autocomplete pipeline by trimming and caching queries, reusing injected services, and exposing sanitized highlight metadata so the dropdown renders without raw `<mark>` tags while delivering faster product, brand, and category lookups.
- Hardened HTML sanitization by removing entire `<script>`, `<style>`, and `<template>` elements instead of unwrapping them, blocking executable payloads from surfacing in storefront or admin renders while keeping safe markup intact.

### Features & Enhancements

- Refactored the localized search results page with a guided hero, contextual metrics, and refreshed empty states so catalog queries like Makita surface faster insights and recovery actions.
- Realigned the Discount Redemption Filament resource navigation metadata and status badge styling with the v4 table schema so admin pages and supporting tests use the modern badge helpers without compatibility gaps.

### Maintenance
* Isolated the database index audit console test on a dedicated SQLite database file, ensuring duplicate-index detection can stage schemas without colliding with the primary test connection while still verifying cleanup flows.
* Registered SearchableInput payload macros lazily with safe defaults and provisioned per-worker SQLite database files during the test bootstrap, eliminating parallel lock contention while keeping hydrate/clear helpers available even when the service provider has not pre-booted macros.
* Extended the demo store seeder to call the collection seeders, ensuring curated collections ship with featured products for storefront demos and automated tests.
* Provisioned a reusable SQLite testing harness that seeds the Spatie permission tables, attribute pivots, and variant matrix schema once per process, registered Filament SearchableInput payload macros for v4 containers, and wrapped the ProductVariant attribute matrix suite in transactions so PHPUnit reuses a shared schema without losing isolation.
* Fixed the custom Filament edit profile page to import the correct Schema class, eliminating fatal compatibility errors during automated tests.
* Normalized Filament navigation icons and groups across pages, resources, relation managers, and widgets to use the BackedEnum-/UnitEnum-aware union types required by Filament v4 so composer installs no longer crash on PHP 8.3.
* Captured the Oct 21–22, 2025 pull request triage results in `docs/analysis/CURRENT_SYSTEM_STATUS.md`, outlining merge-ready Husky and feature flag fixes, superseded Filament cleanups to close, and outstanding follow-up work so maintainers can act without revisiting GitHub filters.
* Updated the test harness configuration to respect the configured database connection so PHPUnit now boots against the shared `database/database.sqlite` datastore by default while remaining overridable for contributors who prefer in-memory runs.
* Captured a repository-wide analysis summary that enumerates the 24 open pull requests, clustering the Filament Schema migrations, Husky shim fixes, and layered rate-limiting work so reviewers can triage without scraping the GitHub UI.
* Documented the open security hardening proposal from PR #289 covering layered API rate limits, per-identity throttling buckets, and correlation-aware logging so stakeholders can track the pending review scope from within the repository knowledge base.
* Migrated Filament resources, relation managers, custom pages, and widgets to the v4 Schema API while normalizing navigation icon docblocks so BackedEnum-powered metadata stays compatible with upstream traits (#1070).
* Refactored the product API flow to run through dedicated application use cases, a presenter, and an Eloquent-backed repository so contract responses stay stable while filtering non-displayable catalogue entries.
* Centralized Filament navigation metadata by adopting the `HasNav` trait on notifications, hardening the Nav helper against recursion, and documenting the `Schema::components([...])` pipeline for the Address resource.
* Normalized the Menu Item Filament resource icon annotation to the shared docblock convention and documented the delegated schema/table builders for reviewers.
* Clarified the Wishlist Item Filament resource navigation metadata by switching to the documented static icon property and explaining the sidebar sort order for reviewers.
* Replaced the artisan diagnostics commands with PHPUnit coverage suites, added Paratest support, and wired a minimum coverage extension that fails the build when thresholds are not met.
* Added granular rate limiting configuration scopes and partner-friendly throttling helpers while wiring CSP nonces into helper utilities and admin providers.
* Normalized HTTP 429 API responses to the new shared `error.rate_limited` problem code and refreshed the contract docs so client throttling logic stays consistent.
* Resolved the cache tagging conflicts from PR #120 by wiring `CacheInvalidationService` into model events, aligning navigation/menu repositories with locale-aware tags, and extending regression tests that exercise storefront widgets and dashboard stats.
* Introduced a cache invalidation service with tag-aware fallbacks and updated storefront widgets to honour locale-aware cache tags while adding regression coverage for cart and dashboard flows.
* Hardened the Filament schema upgrade script so navigation icon docblocks are normalized automatically and every resource/page/widget reflects the v4 schema signature changes.
* Updated the Discount Redemption resource unit test harness to supply a lightweight `HasTable` stub, keeping the Filament v4 table factory invocations compatible with Pest assertions.
* Aligned Filament variant pricing and analytics resources with stricter action namespaces, clarified currency formatting, and refreshed navigation icon annotations to streamline BackedEnum usage across admin pages.
* Synced Collection Rule resource signatures, modal reorder UX, and cache maintenance tooling with Filament v4 to retire legacy array fallbacks.
* Delivered the Campaign Product Target management resource with localized strings, reinforced widget navigation metadata, and hardened media path migrations for safer marketing workflows.
* Restored the Husky bootstrap shim and its executable permissions so Git hooks keep executing with the repository's local toolchain while still surfacing the upstream v10 deprecation guidance.
* Ensured the User Product Interaction Filament resource now returns concrete `Form`/`Table` instances so Filament v4 boots without schema contract errors during analytics validation.
* Normalized Filament navigation icon overrides to rely on docblocks, consolidated variant stock danger badges, and refreshed the `data:import` command metadata to resolve regressions from PR #1098.
* Smoothed out User Product Interaction rating badges and filter option spacing so Filament v4 renders the analytics table without concatenation warnings spotted while reviewing PR #1097.
* Introduced a reusable HTML sanitization pipeline with a maintenance command, model hooks, and storefront renderer updates to harden product and legal content.

### Bug Fixes

- Prevented a Pest-only toggle from leaving `RefreshDatabaseState::$migrated` true for PHPUnit suites and defaulted the news category factory to visible records so the NewsCategory unit tests consistently migrate tables and load scoped relationships.
- Stabilized the cart lifecycle unit suite by provisioning a dedicated lightweight `cart_items` schema during tests, ensuring checkout cleanup assertions run without the full migration stack and continue guarding session/user clearing logic.
- Updated the product catalogue OpenAPI metadata schema to match the presenter payload and documented nullable media fields, eliminating validation mismatches during contract tests.
- Added the missing coupon schema columns (maximum discount caps, per-user limits, and scoped product/category JSON fields) so Laravel migrations and factories align, restoring the API coupon application test suite.
- Rounded coupon discount calculations inside the application service to prevent `Number::parseFloat` type errors when returning pricing payloads during checkout flows.

* Restored the application exception handler so requests and artisan commands stop crashing with `Whoops\\Run::handleShutdown()` when Laravel bootstraps without the class.

- Stabilized the SQLite-powered test bootstrap by forcing an on-disk database, guarding factories against optional columns, and eliminating the `no such table: users` regression that blocked the user attribution observer suite.
- Stabilized analytics event tracking by skipping the user-owned scope in console contexts, gracefully handling missing request/session data, enriching event type listings, and returning float-safe stats so regression suites can assert conversions reliably.
- Made administrative rate limiting, authorization matrix lookups, and brand metadata diagnostics console-friendly by avoiding container-bound config calls and explicitly removing visibility scopes, which restores the targeted unit tests.
- Restored the shipping option ↔ zone relationship so orders, factories, and zone aggregations attach carriers without manual
  attribute overrides during tests.
- Hardened the API search endpoint to short-circuit suspicious payloads and ensure exact-title matches outrank fuzzy results, keeping injection attempts empty while surfacing precise catalogue hits first.

* Ensured the customer and product inline sparkline widgets reuse the cached series datasets and publish matching checksums so Filament tables render the same analytics payload verified by unit tests.

- Normalized search type filters to treat mixed-case input from clients as valid bucket selectors, keeping aggregated storefront results scoped correctly instead of silently reverting to every result category.
- Updated the Attribute Value Filament resource to ignore active/enabled global scopes for admin actions, restoring toggle, duplicate, and bulk activation helpers that previously failed once records were hidden by storefront filters.
- Updated the data import console command regression test to invoke the protected truncation helper via reflection, preserving foreign key enforcement coverage while respecting the command's final modifier.
- Prevented Pest test helper redeclaration errors by wrapping the `login()`, `get()`, and `post()` helpers in existence guards so repeated bootstrap phases during `php artisan test` succeed.
- Expanded the orders status enum and translations to include `confirmed`, `completed`, and return flows so demo seeds and admin filters align with the schema without MySQL truncation warnings during `php artisan migrate:fresh --seed`.
- Hardened the created_at indexing migration with case-insensitive Doctrine checks and driver-specific fallbacks so repeated deployments no longer trip duplicate key errors on tables that already expose timestamp indexes.
- Expanded the currencies schema and demo country seeder translation handling to match model expectations, allowing `php artisan migrate:fresh --seed` to succeed across SQLite/MySQL environments with full multilingual fixtures.
- Added the foundational `customer_groups` table migration so subsequent enhancement scripts (including soft deletes) succeed during fresh installs and automated refreshes.
- Staged the stock reservation foreign keys until the products and variant inventory tables exist so `php artisan migrate:fresh --seed` succeeds on clean installs without sacrificing cascading deletes.
- Deferred discount schema user-account foreign keys until after verifying user table compatibility, eliminating the MySQL `discount_codes_created_by_foreign` system-table failure during migrations restored from production dumps.
- Rebuilt the discount schema migration workflow to toggle MySQL foreign key checks only during data copy, preventing the `discount_codes_created_by_foreign` system-table error encountered when rerunning `php artisan migrate:fresh --seed`.
- Corrected shipping option delivery window formatting so zero-day estimates and partially filled ranges no longer collapse to a placeholder dash in admin tables.
- Restored shipping option zone relationships, eligibility guards, and pricing scopes so unit tests can persist delivery logic without triggering mass-assignment or filtering regressions.
- Ensured the test bootstrap reloads JSON translation directories so Filament commerce navigation labels resolve to localized values instead of falling back to raw keys during regression runs.
- Ensured the Feature Flag resource surfaces inactive and disabled toggles so administrators can audit rollout states without adjusting global scopes.
- Fixed the Activity Log Filament resource navigation icon property by adopting the BackedEnum-aware union type required by Filament v4, preventing fatal errors during admin boot.
- Updated the Analytics dashboard resource to rely on the BackedEnum-aware navigation metadata and `Form` signature expected by Filament v4, resolving the fatal error triggered during panel boot.

### Security

- Introduced a request-scoped CSP nonce service with middleware, Livewire, and Vite integration, hardened permissions/HSTS headers, and refreshed inline Blade assets to comply with nonce-based CSP directives.

## [0.1.0] - 2025-10-20

### Features & Enhancements

- Delivered cart persistence, checkout, coupon application, customer profile management, and locale-aware order return workflows to complete the commerce journey (#102, #103, #101, #168, #230).
- Expanded storefront experiences with modular dashboard widgets, responsive media processing, review interactions, localized catalogue filters, hardened home translations, and refreshed home and search views (#149, #147, #143, #139, #171, #191, #174, #129, #127, #108).
- Introduced API contract validation, versioned routing, branded error handling, and richer domain exceptions for a polished platform surface (#165, #87, #215, #128).
- Added operational integrations including health reporting endpoints, export services, queued stock exports, and deployment cache warming (#194, #178, #180, #228).
- Strengthened admin tooling with Filament API key workflows, centralized authorization policies, and refined navigation defaults (#125, #202, #223).
- Hardened attribution and notification flows through updated feature flag form schemas, user attribution observers, and route model binding (#187, #85, #213).
- Enhanced localization, translation audits, PHPStan coverage, and locale switching for consistent international experiences (#230, #239, #192, #126, #104).
- Bolstered backend resilience with tagged caching, schema cache checks, rebuilt discount schema constraints, and database index auditing (#219, #186, #231, #121).
- Added PHPStan baseline hardening, diagnostics tooling, maintenance command conversions, base Rector configuration, and updater attribution (#240, #214, #211, #99, #98).
- Improved production readiness with security headers, CSP defaults, and logging and error guidance (#233, #236).

### Bug Fixes

- Fixed Filament admin panel topbar defaults and aligned activity log resources with their test coverage (#242, #248, #238).
- Corrected feature flag migrations, column positioning, and icon declarations to keep the admin UI consistent (#247, #169, #166, #133, #132).
- Patched storefront routing and lookup coverage for posts, collections, and profile screens (#141, #140, #138, #107, #91).

### Performance & Scalability

- Optimized data performance with faster list queries, tagged aggregate caching, schema cache checks, and enforced discount constraints (#220, #219, #186, #231).

### Refactors & Code Health

- Realigned data seeding, activity log resources, and attribution models to mirror production behaviour (#248, #237, #224, #134, #123).
- Simplified application maintenance with FormRequest-driven controllers and refactored frontend API handling (#226, #106).

### Documentation

- Centralized documentation into a dedicated analysis hub, quickstart guide, system requirements, collaboration standards, and deployment runbooks (#225, #208, #205, #210, #204).
- Expanded the operational knowledge base with onboarding tooling, API error catalogues, backup and restore guidance, architecture notes, and composer helper references (#179, #182, #189, #190, #198).
- Recorded dependency review status and contribution templates to support project governance (#136, #193).
- Consolidated legacy reports into `docs/analysis/`, moved operational playbooks to `docs/runbooks/`, refreshed the navigation index, and introduced a documentation style guide to keep future contributions consistent.

### Tooling, CI & Maintenance
* Hardened Filament Livewire tests by aliasing schema classes and proxying widget tab Blade components so campaign product target suites pass without manual stubs.
* Automated releases and QA with release linting, Pint normalization, Husky enforcement, and PHP QA improvements (#251, #245, #232, #217).
* Enhanced CI coverage with Lighthouse audits, comprehensive workflows, seeded test runs, and refined Laravel test sequencing (#250, #199, #162, #170).
* Standardized toolchains by pinning Node and Filament dependencies, aligning composer requirements, and expanding Tailwind content globs (#206, #188, #209, #207, #153).
* Cleaned repository state by purging runtime artifacts, stopping tracked builds, removing archived assets and placeholder docs, and replacing environment, cookie, and PHP INI files with templates (#222, #221, #218, #212, #203, #201, #200, #195).
* Enabled security scanning and strengthened automated test coverage with schema mocks and targeted resource tests (#197, #185, #164).
* Added a documentation size guard to CI so oversized Markdown files are caught before merges.

### Legacy Quick Log (formerly `channgelog.md`)

## [Unreleased]
- Resolve cart session clearing conflict by ensuring fallback guest sessions are cleared alongside authenticated sessions.

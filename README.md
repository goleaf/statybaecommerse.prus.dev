# Statybos E-commerce platform

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

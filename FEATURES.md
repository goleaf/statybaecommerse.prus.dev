# Platform Features & Highlights

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
- Variant image records now expose active scopes, metadata formatters, and ordering helpers so storefront galleries and admin tools reference consistent file details.
- Brands page now features a light-themed layout, shared card components, and refreshed translations so the partner directory feels consistent across locales.
- Region-to-city lookups and the dedicated customers table are restored for the SQLite harness, ensuring analytics sparklines, customer factories, and Filament resources can attach geographic metadata without migration errors.
- Localized product/category routing plus collection seeding were hardened, ensuring homepage product links, category landing pages, and collection showcases load without 404s or empty states.
- PHPUnit harness now boots a shared `database/testing.sqlite` schema (including Spatie permission tables and variant attribute pivots) and registers Filament SearchableInput payload macros so admin feature suites stay v4-compatible while reusing deterministic migrations.
- Parallel test execution now provisions per-worker SQLite databases using Laravel's parallel token, and the SearchableInput payload macros register on demand so hydrate/sync helpers avoid TypeErrors even when the provider layer has not initialised them yet.
- Filament dashboard access now defaults to an open posture when no
  permissions are configured and inline sparkline widgets comply with the
  nullable model contract, preventing dashboard and widget regressions.
- Campaign customer segment management now relies on explicit query scopes for
  activity state, campaign, customer group, and segment type so analytics and
  admin listings can intentionally include inactive records without removing
  visibility controls from the model entirely.
- Campaign conversion queries now rely solely on the status-aware scope while
  exposing their translation model and defaulting fresh `converted_at` values, so
  completed, pending, and other lifecycle records remain accessible to analytics
  tooling without fighting an `is_active` filter that the table never exposed.
- Restored the default RefreshDatabase migration flow after the toggleable table Pest suite and ensured the news category factory seeds visible records so unit coverage can assert parent/child/category pivots without global scope interference.
- Catalog contract docs now capture the streamlined product meta payload and nullable media thumbnails so integrators see the same shape published by the API presenter.
- API validation errors now bundle localized violation lists with a fallback English reason so partner integrations can act on stable messaging even when the initial validation precedes locale negotiation.
- Access denied problem responses produced by Symfony's HTTP layer now echo the denial reason inside `error.context.reason`, matching Laravel's authorization payloads and keeping client handlers consistent.
- Test infrastructure now provisions an on-disk SQLite database and conditionally seeds customer group metadata, preventing the observer test suite from failing with missing table or column errors.
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

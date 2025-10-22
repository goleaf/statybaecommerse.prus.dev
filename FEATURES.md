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

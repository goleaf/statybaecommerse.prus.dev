# Statybos E-commerce platform

[![CI](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml/badge.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/badge/coverage-manual--run-lightgrey.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)

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

### Latest updates
- Husky Git hook bootstrap script is restored so local commits keep running repository-defined quality checks while still surfacing the upstream deprecation warning for teams preparing for Husky v10.
- Shipping option management screens now present accurate delivery windows even when the minimum day value is zero or one side of the range is missing, giving merchandisers clearer expectations when auditing carriers.

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
   After you tweak environment values, rebuild the configuration cache so Laravel sees the changes:
   ```bash
   php artisan config:cache
   ```
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

## Latest maintenance

- 2025-10-21: Tightened the User Product Interaction Filament resource to return concrete `Form` and `Table` instances so Filament v4 boots cleanly when analytics interactions are seeded.

## Architecture cheatsheet

| Area | Location(s) | Notes |
| --- | --- | --- |
| HTTP entrypoints | `routes/web.php`, `routes/api.php`, `routes/admin.php` | Inertia/Volt storefront routes live in `web.php`; Filament registers admin routes in `AdminPanelProvider`. |
| Filament admin | `app/Filament/**` | Resources, widgets, custom pages, and global actions follow Filament v4 conventions outlined in [docs/analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md](docs/analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md). |
| Domain models & data | `app/Models`, `app/Data`, `database/migrations`, `database/factories`, `database/seeders` | Data objects use `spatie/laravel-data`; factories and multilingual seeders ensure parity between Lithuanian and English content. |
| Business services | `app/Services`, `app/Actions`, `app/Support` | Encapsulated workflows (pricing, availability, marketing, search) with helper traits for caching and localization. |
| Background processing | `app/Jobs`, `app/Listeners`, `app/Notifications`, `app/Console` | Horizon manages queues; recurring tasks registered through `Console/Kernel.php`. |
| Frontend assets | `resources/views`, `resources/js`, `resources/css`, `tailwind.config.js`, `vite.config.js` | Uses Tailwind v4 + Vite; Livewire Volt pages bridge server-driven UI to the storefront. |
| Quality & automation | `Makefile`, `composer.json` scripts, `package.json` scripts, `scripts/*.mjs`, `autofix-realtime.sh` | Make targets wrap Composer/NPM scripts; MCP tooling (`mcp/filament-docs-server.js`) serves Filament component docs locally. |

> ℹ️ **Git hook reliability**: Husky's bootstrap shim (`.husky/_/husky.sh`) is kept executable and aligned with the legacy v9 behavior so our pre-commit, lint, and formatting hooks continue to run even while Husky v10 emits deprecation banners.

## Data and integration dependencies
- **Caching & queues**: Redis (or Predis) backing Horizon for real-time metrics and queue execution.
- **Search**: Toggle between database and Laravel Scout engines with `SEARCH_DRIVER`; keep `SCOUT_ENABLED=false` locally for SQL fallback, or enable Scout for Algolia/Meilisearch and rebuild indexes via `php artisan search:index`.
- **Media**: `spatie/laravel-medialibrary` manages product imagery, generated conversions, and downloads.
- **PDF & exports**: `barryvdh/laravel-dompdf` and `pxlrbt/filament-excel` power report exports from the admin panel.

## One-liners for build, quality, and tests
| Task | Command |
| --- | --- |
| Run feature & unit tests | `make test` |
| Static analysis | `make analyse` |
| PHP formatting | `make format` |
| Rector dry run | `composer rector -- --dry-run` |
| Build production assets | `make build` |
| Generate coverage locally | `php artisan test --coverage` |

## Latest maintenance notes
- Re-applied the documented docblock pattern for Filament navigation icons to stay aligned with v4 schema upgrades after the #533 regression.
- Consolidated variant stock history danger badge mappings so both damage and theft events share the intended styling.
- Clarified the `data:import` command metadata with typed properties to improve Artisan list readability.

## Composer script quick reference
| Script | What it does |
| --- | --- |
| `composer ci` | Runs PHPStan (`phpstan analyse`) then executes the CI-friendly PHPUnit suite (`phpunit --log-junit ...`). |
| `composer analyse` | Alias of `composer analyze` for PHPStan static analysis. |
| `composer seed:fresh` | Proxies `php artisan migrate:fresh --seed --ansi` to rebuild the database with demo data. |
| `composer build` | Calls `php artisan optimize --ansi` before `npm run build` to prep caches and assets. |
| `composer serve` | Uses `php artisan serve --ansi` for a local HTTP server. |

## Configuration notes
- Environment defaults live in `.env.example`; copy it to `.env` to tweak database/queue/mail settings.
- SQLite is enabled by default for fast onboarding—switch `DB_CONNECTION` in `.env` if you need MySQL/PostgreSQL.
- Storage symlink (`public/storage`) is created by `make setup`; re-run `php artisan storage:link` if you remove it.
- Horizon, Scout, and media-processing queues expect Redis; fall back to the sync driver for local smoke testing by setting `QUEUE_CONNECTION=sync`.
- Rebuild search indexes for Scout with `php artisan search:index --fresh` or schedule zero-downtime refreshes through `php artisan search:index:rebuild`.
- Frontend assets rely on modern Node (20+) with native ESM; ensure `npm install` runs before invoking Vite or Playwright scripts.

## Further reading
- Start with [docs/INDEX.md](docs/INDEX.md) for a curated guide to deployment runbooks, feature deep-dives, and historical archives.
- Need domain-level context? Check [docs/analysis/COMPANY_RESOURCE_ANALYSIS.md](docs/analysis/COMPANY_RESOURCE_ANALYSIS.md) and the project summaries collected under [docs/analysis/](docs/analysis/).
- Want a system tour? Review [docs/ARCHITECTURE_OVERVIEW.md](docs/ARCHITECTURE_OVERVIEW.md) for component breakdowns and integration diagrams, then keep the runbooks in [docs/runbooks/](docs/runbooks/) close for operational workflows.

Happy building!

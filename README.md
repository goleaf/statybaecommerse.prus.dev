# Statybos E-commerce platform

[![CI](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml/badge.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/badge/coverage-manual--run-lightgrey.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)

## What it is
A multilingual Laravel 12 + Filament v4 storefront and admin panel for managing construction-product catalogues, analytics, and operations for statybaecommerse.prus.dev. The repository ships extensive Filament resources, analytics dashboards, and seeders so you can explore the platform locally without extra setup.

### Key capabilities at a glance
- **Product catalogue management** with rich attribute, variant, bundle, and availability tooling powered by Filament resources.
- **Customer- and order-centric workflows** including loyalty, referral, and recommendation engines surfaced through reusable services and widgets.
- **Content & marketing** features such as news, landing pages, SEO metadata, and email campaign tooling with automated translations.
- **Operational dashboards** for activity logs, analytics, and background job health leveraging Laravel Horizon, Scout, and bespoke widgets.
- **Multilingual experience** across storefront and admin via `spatie/laravel-translatable`, Volt-powered Livewire pages, and localized seed data.

## Requirements
- PHP 8.2+ with `ext-sqlite3`, `ext-fileinfo`, and `ext-gd`
- Composer 2.6+
- Node.js 20+ with npm 10+
- SQLite (default local database) or MySQL/PostgreSQL if you prefer
- Make (optional but recommended for the helper targets below)

## Quick start (Make helpers + dev stack)
1. **Clone & bootstrap**
   ```bash
   git clone <repo-url>
   cd statybaecommerse.prus.dev
   make setup
   ```
   This installs PHP and Node dependencies, prepares a fresh `.env`, provisions a SQLite database, and links storage symlinks.
2. **Run migrations**
   ```bash
   make migrate
   ```
3. **Seed minimal data (creates admin user and system settings)**
   ```bash
   make seed
   ```
   Admin credentials: `admin@statybaecommerse.prus.dev` / `admin123`.
4. **Start the app**
   - Lightweight PHP server:
     ```bash
     make serve
     ```
   - Full experience (PHP server, queue listener, pail, Vite) via the existing Composer script:
     ```bash
     make dev
     ```
5. **Visit the site**
   - Storefront: http://127.0.0.1:8000/
   - Admin panel: http://127.0.0.1:8000/admin (log in with the seeded admin user)

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

## Data and integration dependencies
- **Caching & queues**: Redis (or Predis) backing Horizon for real-time metrics and queue execution.
- **Search**: Laravel Scout ready for Algolia/Meilisearch; disable or swap drivers via `.env`.
- **Media**: `spatie/laravel-medialibrary` manages product imagery, generated conversions, and downloads.
- **PDF & exports**: `barryvdh/laravel-dompdf` and `pxlrbt/filament-excel` power report exports from the admin panel.

## One-liners for build, quality, and tests
| Task | Command |
| --- | --- |
| Run feature & unit tests | `make test` |
| Static analysis | `make analyse` |
| PHP formatting | `make format` |
| Build production assets | `make build` |
| Generate coverage locally | `php artisan test --coverage` |

## Configuration notes
- Environment defaults live in `.env.example`; copy it to `.env` to tweak database/queue/mail settings.
- SQLite is enabled by default for fast onboarding—switch `DB_CONNECTION` in `.env` if you need MySQL/PostgreSQL.
- Storage symlink (`public/storage`) is created by `make setup`; re-run `php artisan storage:link` if you remove it.
- Horizon, Scout, and media-processing queues expect Redis; fall back to the sync driver for local smoke testing by setting `QUEUE_CONNECTION=sync`.
- Frontend assets rely on modern Node (20+) with native ESM; ensure `npm install` runs before invoking Vite or Playwright scripts.

## Further reading
- Start with [docs/INDEX.md](docs/INDEX.md) for a curated guide to deployment runbooks, feature deep-dives, and historical archives.
- Need domain-level context? Check [docs/analysis/COMPANY_RESOURCE_ANALYSIS.md](docs/analysis/COMPANY_RESOURCE_ANALYSIS.md) and the project summaries in `docs/`.
- Want a system tour? Review [docs/ARCHITECTURE_OVERVIEW.md](docs/ARCHITECTURE_OVERVIEW.md) for component breakdowns and integration diagrams.

Happy building!

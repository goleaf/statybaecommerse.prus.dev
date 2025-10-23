# Architecture Overview

This document describes how the Statybos e-commerce platform is organised so agents can reason about changes quickly.

## High-level system map

| Layer | Responsibilities | Notable packages |
| --- | --- | --- |
| **Presentation** | Storefront pages, admin panel screens, Livewire/Volt interactions, queued notifications. | Filament v4, Livewire Volt, Tailwind v4, Vite, Laravel Notifications |
| **Application** | Actions, form/table builders, DTOs, caching helpers, cross-cutting rules (validation, policies). | `spatie/laravel-data`, `spatie/laravel-permission`, `spatie/laravel-activitylog` |
| **Domain** | Product, pricing, marketing, content, news, recommendation services and models. | Custom `App\Services\*`, `App\Models\*` |
| **Infrastructure** | Queue workers, Horizon, Scout search, media handling, import/export, PDF generation. | Redis/Predis, Laravel Horizon, Laravel Scout, Spatie Media Library, Filament Excel, DOMPDF |

Laravel 12 provides the HTTP kernel, queue worker, and scheduler. Filament v4 supplies the admin panel shell and most CRUD scaffolding. The storefront uses server-rendered Blade + Volt components paired with Tailwind/Vite assets.

## Backend structure

- `app/Filament/` — Filament resources, widgets, actions, and custom pages. Resources follow v4 signatures and lean on `Form`/`Table` builders to expose product, marketing, and operations data.
- `app/Http/Controllers/` & `routes/*.php` — REST controllers and route definitions. `routes/admin.php` adds Filament-specific routing while `routes/web.php` handles storefront flows.
- `app/Services/` — Coarse-grained services (pricing engines, recommendation systems, marketing automation). Many services accept DTOs from `app/Data/` and emit events consumed by listeners in `app/Listeners/`.
- `app/Actions/` — Reusable command objects invoked by jobs, controllers, or Filament actions. Actions encapsulate multi-step workflows (e.g., syncing external catalogues).
- `app/Support/` — Helper classes and traits shared across services/resources, including caching helpers, locale utilities, and feature toggles.
- `app/Support/Authorization/AuthorizationMatrix.php` now inspects the active Filament panel to resolve the correct guard, and safely falls back to configuration defaults whenever the Filament container binding is unavailable (e.g., storefront queues), ensuring admin-only policies keep working even if `filament.auth.guard` is not explicitly configured.
- `app/Jobs/` — Queueable jobs for imports, report generation, and notification delivery. Horizon monitors these queues; configuration lives in `config/horizon.php`.
- `app/Observers/` & `app/Events/` — Domain events driving audit trails via `spatie/laravel-activitylog` and asynchronous side effects.

### Data layer

- `app/Models/` — Eloquent models with relationships, casts, and scopes. Traits in `app/Traits/` and `app/Collections/` add domain-specific behaviour.
- `database/migrations/` — Schema definitions with multilingual columns, JSON attributes, and pivot tables for catalog associations.
- `database/factories/` & `database/seeders/` — Factory blueprints and seeders. City, product, and marketing fixtures are split into dedicated seeders to keep Lithuanian and English datasets aligned.
- `app/Data/` — Data-transfer objects (DTOs) using `spatie/laravel-data` to normalise payloads between controllers, services, and jobs.

### Background processing & integrations

- `app/Console/Kernel.php` schedules cron-style jobs (e.g., syncing inventory, sending digests).
- `config/horizon.php`, `config/queue.php` manage queue connections. Redis is the default; `.env` toggles allow falling back to the sync driver for smoke testing.
- `app/Notifications/`, `resources/views/mail/` define transactional email/SMS templates.
- `scripts/*.mjs` host Playwright and diagnostic routines for monitoring route output and console health.

## Frontend stack

- `resources/views/` — Blade templates for storefront and admin mailables. Livewire Volt components sit under `resources/views/livewire` for reactive widgets.
- `resources/js/` — Vite entrypoints, Stimulus-style controllers, and Filament-specific enhancements. Uses native ES modules.
- `resources/css/` & `tailwind.config.js` — Tailwind v4 configuration, leveraging plugin set (`forms`, `typography`, `aspect-ratio`).
- `vite.config.js` — Vite pipeline bridging Laravel's asset helper with modern bundling.

## Tooling & automation

- `Makefile` — Wraps composer/npm commands for setup, quality checks, database resets, and dev-server orchestration.
- `composer.json` scripts — Provide QA loops (`lint:php`, `analyze`, `fix`), docs maintenance (`docs:*`), and queue/cache helpers.
- `package.json` scripts — Frontend build/dev plus MCP utilities (`mcp:filament`) and smoke-test runners (`scripts/e2e-*.mjs`).
- `autofix-realtime.sh` — Launches the real-time autofix workflow described in `docs/REALTIME_AUTOFIX_GUIDE.md`.

## Development quick reference

- Use `make dev` for an all-in-one dev environment (PHP server, queue listener, Laravel Pail logs, Vite dev server).
- Run `make analyse` before committing to combine Pint (style) and PHPStan (static analysis).
- Horizon dashboard available at `/horizon` when running the queue worker.
- Dusk/browser tests require `php artisan serve` + ChromeDriver; see `docs/TEST_ORGANIZATION_SUMMARY.md` for patterns.
- Explore domain history and project evolution through the curated summaries in `docs/` — start with `docs/PROJECT_HANDOVER_DOCUMENTATION.md` and `docs/analysis/COMPANY_RESOURCE_ANALYSIS.md`.

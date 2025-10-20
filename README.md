# Statybos E-commerce platform

[![CI](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml/badge.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/badge/coverage-manual--run-lightgrey.svg)](https://github.com/prus-dev/statybaecommerse.prus.dev/actions/workflows/ci.yml)

## What it is
A multilingual Laravel 12 + Filament v4 storefront and admin panel for managing construction-product catalogues, analytics, and operations for statybaecommerse.prus.dev. The repository ships extensive Filament resources, analytics dashboards, and seeders so you can explore the platform locally without extra setup.

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

## Further reading
- Start with [docs/INDEX.md](docs/INDEX.md) for a curated guide to deployment runbooks, feature deep-dives, and historical archives.
- Need domain-level context? Check [COMPANY_RESOURCE_ANALYSIS.md](COMPANY_RESOURCE_ANALYSIS.md) and the project summaries in `docs/`.

Happy building!

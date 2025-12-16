---
inclusion: manual
---

# Large refactors / upgrades (manual)

Load this file only when you explicitly ask for a broad refactor (e.g. “upgrade Filament”, “remove legacy package”, “build full e‑commerce flows”).

## Operating mode
- Work in small loops: change → targeted tests → fix.
- Prefer refactoring existing code over generating new scaffolds (avoid `php artisan make:*` unless explicitly requested).
- Don’t add migrations unless necessary; if schema changes, update factories/seeders/views/resources in the same loop.

## E‑commerce scope (high-level)
- Admin: Filament resources/pages/widgets with policies and good UX.
- Storefront: Livewire components for catalog → cart → checkout → orders.
- Parity: when removing legacy packages, preserve behavior and backfill tests.

## Tooling
- Use Laravel Boost MCP tools (`application-info`, `search-docs`, `list-routes`, `read-log-entries`, `browser-logs`) to avoid guessing.

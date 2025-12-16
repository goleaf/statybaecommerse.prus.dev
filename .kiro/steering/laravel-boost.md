---
inclusion: fileMatch
fileMatchPattern: '**/*.php'
---

<laravel-boost-guidelines>

# Laravel Boost (MCP) workflow

## Use the tools (avoid guessing)
- `application-info`: versions, packages, models.
- `search-docs`: version-specific docs for Laravel/Filament/Livewire/etc.
- `list-routes`, `get-absolute-url`: route and URL correctness.
- `database-schema`, `database-query`: read-only DB inspection.
- `last-error`, `read-log-entries`: backend debugging.
- `browser-logs`: frontend debugging.

## Project conventions
- Laravel 12 skeleton (no `app/Http/Kernel.php`, no `app/Console/Kernel.php`).
- Prefer Pest tests and a narrow test filter first (`php artisan test --filter=...`).
- Run `vendor/bin/pint` when formatting is needed (prefer `--dirty` when available).

</laravel-boost-guidelines>

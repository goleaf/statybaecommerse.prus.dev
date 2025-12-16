---
inclusion: always
---

# Project Steering (egistatyba)

## Docs & tools
- Prefer official docs: `https://filamentphp.com/docs` and `https://laravel.com/docs/12.x/`.
- Prefer MCP (Laravel Boost) tools for introspection (routes, schema, logs, config, docs) before guessing.

## Stack (source of truth)
- Laravel 12, Filament 4, Livewire 3, TailwindCSS 4.
- PHP target/version constraints live in `composer.json` (`config.platform.php`).

## Workflow
- Keep changes minimal and consistent with existing conventions in this repo (check sibling files).
- Prefer small loops: change → targeted test → fix → repeat.

## Commands / generators
- Avoid `php artisan make:*` scaffolding for app code unless explicitly requested.
- OK: running tests (`php artisan test`) and formatting (`vendor/bin/pint`).

## Localization + money
- Every new user-facing string must have both `lt` and `en` translations.
- Default language: Lithuanian (`lt`). Default currency: EUR.
- Add translation keys using snake_case, inside existing files under `resources/lang/{lt,en}` (don’t create new locale folders unless asked).

## Naming
- Do not introduce new `advanced-*` / `enhanced-*` prefixed files; merge into existing modules instead.

## Git
- Never commit or push unless explicitly asked.

# Repository Guidelines

## Project Structure & Module Organization
- `app/` Laravel application code (controllers, models, services, policies).
- `routes/` HTTP, console, and API routes.
- `database/` migrations, factories, and seeders (including `database/seeders/cities/`).
- `resources/` Blade views and Vite entrypoints; built assets in `public/`.
- `tests/` Pest/PHPUnit suites (`Unit`, `Models`, `Feature`, `Admin`, `Performance`).
- `docs/`, `scripts/`, and `assets/` for docs, tooling, static assets; `storage/` and `bootstrap/cache` are runtime-only.

## Build, Test, and Development Commands
- `composer install`, `npm install` to set up dependencies.
- `composer run dev` (Laravel server + queue + logs + Vite); `composer run serve` (server only).
- `npm run dev` for Vite; `composer run build` for optimized builds.
- `composer run test` (Pest), `composer run test:ci` (PHPUnit + coverage).
- `composer run analyze` (PHPStan); `composer run lint:php` / `composer run fix:php` (Pint).

## Coding Style & Naming Conventions
- 4-space indentation, LF, final newline (`.editorconfig`).
- PHP uses `declare(strict_types=1);` in new files and explicit return types.
- PSR-4 classes under `App\\` in `app/`; use descriptive names.
- JS uses ESLint + Prettier (`printWidth: 100`, single quotes, semicolons).
- Format PHP with Pint (Laravel preset + project rules).

## Testing Guidelines
- Pest 3 is primary; create with `php artisan make:test --pest`.
- Tests are `*Test.php` under `tests/` suites; run targeted tests with `php artisan test --compact` (file or `--filter`).
- CI enforces coverage thresholds (minimum 65/70% via PHPUnit extensions).

## Commit & Pull Request Guidelines
- Conventional Commits in history (e.g., `feat: ...`, `feat(filament): ...`, `upgrade`); prefer `type(scope): summary`.
- PRs: short description, linked issues, test notes, screenshots for UI/admin changes, and any config/migration steps.

## Security & Configuration Tips
- Copy `.env.example` to `.env`; never commit secrets.
- Use `composer run app:install` for migrations and storage symlink; use `config('...')` in app code (avoid `env()` outside config).

## OpenSpec Workflow (Agent-Specific)
- For new features, breaking changes, or architecture/perf/security work, create an OpenSpec change in `openspec/changes/<id>/` and follow `openspec/AGENTS.md`.
- Do not implement until the proposal is approved; validate with `openspec validate --strict`.

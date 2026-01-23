# Repository Guidelines

## Project Structure & Module Organization
- `app/` holds Laravel application code (controllers, models, services, policies).
- `routes/` defines HTTP, console, and API routes.
- `database/` contains migrations, factories, and seeders (including `database/seeders/cities/`).
- `resources/` holds Blade views plus Vite JS/CSS entrypoints; built assets go to `public/`.
- `tests/` contains Pest and PHPUnit suites (Unit, Models, Feature, Admin, Performance).
- `docs/`, `scripts/`, and `assets/` store documentation, tooling scripts, and static assets.
- `storage/` and `bootstrap/cache` are runtime folders and should stay out of version control.

## Build, Test, and Development Commands
- `composer install` and `npm install` set up PHP and Node dependencies.
- `composer run dev` starts the Laravel server, queue worker, log tailing, and Vite in parallel.
- `composer run serve` runs the Laravel dev server only.
- `npm run dev` runs Vite for frontend assets.
- `composer run build` optimizes Laravel and builds Vite assets.
- `composer run test` runs the Pest test suite.
- `composer run test:ci` runs PHPUnit with JUnit and coverage output.
- `composer run analyze` runs PHPStan; `composer run lint:php` / `composer run fix:php` run Pint.
- `npm run e2e:smoke` runs a lightweight Playwright smoke check.

## Coding Style & Naming Conventions
- Indentation is 4 spaces, LF line endings, and final newlines (`.editorconfig`).
- PHP is formatted with Pint (Laravel preset + project rules); use strict types in new files.
- JavaScript uses ESLint (`eslint.config.js`) and Prettier (`printWidth: 100`, single quotes, semicolons).
- Classes follow PSR-4 (`App\\` in `app/`), and tests use the `*Test.php` suffix.

## Testing Guidelines
- Pest is the primary runner (`composer run test`), with suites defined in `phpunit.xml`.
- Tests live in `tests/Unit`, `tests/Models`, `tests/Feature`, `tests/Admin`, `tests/Performance`.
- CI enforces coverage thresholds (minimum 65/70% via PHPUnit extensions).
- Tests use SQLite in-memory by default; avoid hard-coding external services.

## Commit & Pull Request Guidelines
- Commit messages should follow Conventional Commits (commitlint types include `feat`, `fix`, `chore`, `docs`, `refactor`, `test`, etc.).
- Prefer `type(scope): summary` (example: `feat(filament): add pricing rule editor`).
- PRs should include a short description, linked issues, and test notes.
- Add screenshots for UI or admin panel changes, and note any config or migration steps.

## Configuration & Security Notes
- Copy `.env.example` to `.env`; never commit secrets.
- `composer run app:install` applies migrations and creates the storage symlink.

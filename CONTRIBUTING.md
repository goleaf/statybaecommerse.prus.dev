# Contributing

## Branching & workflow

- Target `main` with feature branches named `feature/<slug>` or `fix/<slug>`.
- Keep commits small and purposeful; describe the behavioural change and any skipped checks in the message body.
- Use the PR template in `.github/PULL_REQUEST_TEMPLATE.md` and include links to relevant docs or tracking tickets.

## Local environment

- Run `make setup` after cloning to install Composer/NPM dependencies, generate `.env`, and prepare the SQLite database.
- `make dev` launches PHP, queue listener, Pail logs, and Vite in one terminal. Use `QUEUE_CONNECTION=sync` if Redis is unavailable locally.
- Horizon is accessible at `http://127.0.0.1:8000/horizon` when the queue worker is running.

## Quality gates

- Formatting: `make format` (wraps Pint) or `composer fix:php` for verbose output.
- Static analysis: `make analyse` or `composer analyze` (PHPStan with repo config).
- Rector adjustments: run `composer rector` on targeted paths when performing framework upgrades.
- Blade caches: when editing Blade templates, execute `php artisan view:clear && php artisan view:cache`.

## Testing

- Run the automated test suite with `composer test`. This executes the project-standard PHPUnit configuration and stores local coverage data in `storage/app/coverage`.
- Continuous integration uses `composer test:ci` to generate machine-readable reports in the `build/` directory. Use it locally when you need JUnit or Clover artifacts.
- For scoped runs, prefer `php artisan test --filter=<ClassName>` or target directories (see `tests/README.md` for layout).
- Browser/Dusk tests require ChromeDriver (`composer dusk:chrome`) and a running `php artisan serve` instance.

## Documentation & knowledge base

- Update `docs/CHANGELOG.md` with user-visible features, migrations, or dependency bumps.
- For new architectural areas, add a short pointer to `docs/INDEX.md` to keep the navigation fresh.
- Align cache additions with the [Cache Policy](docs/CachePolicy.md) and mention notable cache keys in PR descriptions when relevant.

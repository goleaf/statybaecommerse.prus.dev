# Model Tests Runbook

The model-focused regression suite is a quick indicator that Eloquent scopes, casts, and relationships remain stable. Use the following checklist when preparing a patch that touches any Laravel model:

1. Install PHP dependencies if the `vendor/` directory is missing:
   ```bash
   composer install
   ```
2. Execute the model test suite. Running the folder keeps execution time reasonable while covering the bulk of behavioural contracts:
   ```bash
   php artisan test tests/Models
   ```
   The PHPUnit configuration now registers `tests/Models` as its own suite, so invoking the folder (or the `Models` suite) avoids duplicate file discovery warnings during broader runs.
3. When debugging individual failures, target the specific file for faster feedback. For example:
   ```bash
   php artisan test tests/Models/ActivityLogTest.php
   ```
4. If a test needs to toggle global configuration (for example disabling `activitylog.enabled` to speed up fixtures), capture the original value and restore it in `tearDown()`/`afterEach`. Leaving the toggle mutated causes unrelated suites—like the system setting category activity assertions—to fail unexpectedly.
5. After the suite succeeds, review `junit.xml`. The tooling will repoint the file to the latest run; restore it if the diff only reflects timing metadata so Git history stays readable.
6. Capture any non-obvious insights or new invariants in the relevant markdown audits inside `docs/analysis/` so future tasks have the context baked in.
7. When overriding query builders (for example, removing global scopes in a Filament resource to expose soft-deleted or hidden rows), document the intent with an `@return Builder<Model>` annotation so PHPStan retains its generic context and other engineers remember why moderation tools bypass the storefront defaults.
8. Global scopes now re-validate their cached schema metadata after migrations complete, so if a model suddenly surfaces unexpected rows (for example, `CustomerGroup::enabled()` returning disabled fixtures) rerun the test after the migration phase to let the refreshed cache take effect rather than patching around stale `is_active`/`is_enabled` filters.

## System setting attribution safety

- Unit tests that spin up `SystemSetting` records should temporarily blank the attribution configuration keys (`attribution.system_user_id`, `attribution.system_user_email`, and `attribution.system_user_name`).
- The observer registered in `App\Providers\AppServiceProvider` backfills these columns with a “system” account, and without the override SQLite raises foreign key errors because no such user exists inside the in-memory harness.
- Applying the config override in the test `setUp()` keeps the observer idle while still exercising the same evaluation logic the production code relies on.

## Dashboard Fixture Placeholders

- The CI dashboards and historical analytics still expect `Tests\Feature\ExampleTest` and
  related suites to exist. Lightweight placeholder files now live in `tests/Feature`,
  `tests/Unit`, `tests/Livewire`, `tests/Filament`, and `tests/Http`. Keep these examples in
  place (and green) so progress reports render without 404s when polling the project-level
  test index.

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
   - When adding new Pest files under `tests/Models`, import `Tests\\TestCase` and call `uses(TestCase::class);` so the shared SQLite harness registers the Eloquent connection before factories execute.
3. When debugging individual failures, target the specific file for faster feedback. For example:
   ```bash
   php artisan test tests/Models/ActivityLogTest.php
   ```
4. Pest-style model specs that interact with facades (for example calling `Schema::hasColumn()` inside `tests/Models/UserOrderingPestTest.php`) must opt into the base `Tests\\TestCase` so Laravel boots the application container. Pair it with the usual database trait to keep refresh behaviour consistent:
   ```php
   uses(TestCase::class, RefreshDatabase::class);
   ```
   Without the test case binding, facade calls will raise “A facade root has not been set.” during execution.
5. If a test needs to toggle global configuration (for example disabling `activitylog.enabled` to speed up fixtures), capture the original value and restore it in `tearDown()`/`afterEach`. Leaving the toggle mutated causes unrelated suites—like the system setting category activity assertions—to fail unexpectedly.
6. After the suite succeeds, review `junit.xml`. The tooling will repoint the file to the latest run; restore it if the diff only reflects timing metadata so Git history stays readable.
7. Capture any non-obvious insights or new invariants in the relevant markdown audits inside `docs/analysis/` so future tasks have the context baked in.
   - Order factories now lean on deterministic counters and core PHP helpers instead of Faker formatters because the bundled generator ships without the legacy providers; mirror the counter-driven identifiers and static address data when crafting bespoke fixtures so CI environments stay stable.
   - Filament table actions that rely on modal forms now require Livewire tests to mount the action, populate the form state, and then execute the mounted action. Use the `mountTableAction`/`setTableActionData` helpers (or their bulk equivalents) instead of calling `callTableAction` with raw data so validation hooks receive the intended payload.
   - When Filament resources are guarded by policies, seed the corresponding Spatie permissions before booting the Livewire component so authorization checks pass deterministically during tests (see `tests/Admin/CustomerResourceTest.php`).【F:tests/Admin/CustomerResourceTest.php†L21-L52】【F:tests/Admin/CustomerResourceTest.php†L337-L360】
8. When overriding query builders (for example, removing global scopes in a Filament resource to expose soft-deleted or hidden rows), document the intent with an `@return Builder<Model>` annotation so PHPStan retains its generic context and other engineers remember why moderation tools bypass the storefront defaults.
   - The `PostApproval` relationship now calls `withoutGlobalScopes()` to ensure moderation history links back to posts that are still drafts or archived while `Tests\Models\PostApprovalTest` verifies relationship hydration across unpublished content.【F:app/Models/PostApproval.php†L34-L50】【F:tests/Models/PostApprovalTest.php†L32-L61】
9. Global scopes now re-validate their cached schema metadata after migrations complete, so if a model suddenly surfaces unexpected rows (for example, `CustomerGroup::enabled()` returning disabled fixtures) rerun the test after the migration phase to let the refreshed cache take effect rather than patching around stale `is_active`/`is_enabled` filters.
   - `Partner::getEffectiveDiscountRateAttribute()` and its commission twin now bypass the active/enabled scopes when lazily resolving a tier so model specs that null out the partner rate still inherit the historical tier values even if factories create a disabled tier record.【F:app/Models/Partner.php†L161-L207】
10. Customer group activation toggles still mirror `false` assignments across both `is_active` and `is_enabled` when only one value is provided, but the mutators now respect explicit divergence when both flags are supplied (for example `is_enabled = true` with `is_active = false`). The setters now inspect the dirty state to decide whether a value was explicitly provided in the same payload, so update any bespoke fixtures that rely on the previous always-cascade behaviour.【F:app/Models/CustomerGroup.php†L545-L612】
11. The SQLite fallback schema used by `Tests\\Support\\TestingDatabase` now provisions lightweight `products` and `product_images` tables whenever the full migration stack cannot execute. This keeps the product catalogue factories and `ProductImage` unit coverage operational even when the harness regenerates an abbreviated database, so include those columns in new assertions when expanding the suite.
12. XML catalogue imports now seed default `status` and `published_at` values so `Product` records remain discoverable through the `ActiveScope` and `PublishedScope` filters during regression runs, preventing soft failures when round-tripping fixture data via `XmlCatalogService`.【F:app/Services/XmlCatalogService.php†L382-L391】

## System setting attribution safety

- Unit tests that spin up `SystemSetting` records should temporarily blank the attribution configuration keys (`attribution.system_user_id`, `attribution.system_user_email`, and `attribution.system_user_name`).
- The observer registered in `App\Providers\AppServiceProvider` backfills these columns with a “system” account, and without the override SQLite raises foreign key errors because no such user exists inside the in-memory harness.
- Applying the config override in the test `setUp()` keeps the observer idle while still exercising the same evaluation logic the production code relies on.
- `Tests\\Unit\\SystemSettingDependencyConditionTest` now centralises helper methods (`createSystemSetting`, `createDependency`) that call `createQuietly()` and eagerly load relations, so reuse them when expanding the suite to avoid repeating observer workarounds.
- Feature flag unit coverage follows a similar pattern: seed an attributed user (or clear the attribution config) before creating `FeatureFlag` factories so the observer can satisfy the `created_by` foreign key on SQLite.

## Schema guard rails for SQLite migrations

- The `2025_02_01_000002` system setting dependency migration now checks for existing columns and drops the legacy `system_setting_dependencies_condition_index` before removing the original JSON payload so `TestingDatabase::migrate()` can run repeatedly without tripping duplicate-column or missing-index errors on SQLite.
- The `2025_09_09_000000` discounts migration conditionally attaches the `zone_id` foreign key only when the zones table already exists, falling back to an index otherwise, which keeps early schema runs from failing before the zone migrations execute.

## Dashboard Fixture Placeholders

- Historical dashboards still surface `Tests\Feature\ExampleTest` identifiers, but the
  source data now ships exclusively through the JSON fixtures consumed by the test results
  feature tests. Maintain the mocked payloads in `tests/Feature/TestResults*` so the
  expected identifiers remain visible even though the dedicated feature test class has been
  retired. Continue keeping the remaining placeholder files in `tests/Unit`,
  `tests/Livewire`, `tests/Filament`, and `tests/Http` green so the progress reports stay
  consistent.

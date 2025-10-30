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
- Full database refreshes automatically run `Database\\Seeders\\AllSeedersSeeder`, which discovers and executes every seeder class in the tree. Reach for `php artisan db:seed --class=...` when a focused dataset keeps feedback loops manageable.【F:database/seeders/AllSeedersSeeder.php†L7-L109】【F:config/seeds.php†L28-L44】
   - The `User::activityLogs()` relation now resolves through a morphMany association, so any bespoke activity log seeders should call `$user->activityLogs()` instead of querying `ActivityLog::where('user_id', ...)` to avoid “no such column: activity_log.user_id” errors when the SQLite harness is active.【F:app/Models/User.php†L741-L749】【F:database/seeders/ActivityLogSeeder.php†L14-L36】
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
6. Redis is now the default cache backend in production, and component performance metrics share the same store, so ensure your local environment has Redis running or override `CACHE_STORE`/`OBSERVABILITY_METRICS_CACHE_STORE` to `array` when running isolated scripts outside PHPUnit's provided overrides.【F:config/cache.php†L17-L75】【F:config/observability.php†L1-L10】【F:app/Services/Shared/ComponentPerformanceService.php†L17-L95】
7. After the suite succeeds, review `junit.xml`. The tooling will repoint the file to the latest run; restore it if the diff only reflects timing metadata so Git history stays readable.
8. Capture any non-obvious insights or new invariants in the relevant markdown audits inside `docs/analysis/` so future tasks have the context baked in.
- The test harness now provisions a deterministic application key and exposes the Slider Analytics navigation label via the
  shared error template so Filament dashboard assertions can execute without failing on missing environment state or
  asynchronous widget hydration. Keep those helpers in mind if you migrate to a bespoke testing bootstrap.
- Order factories now lean on deterministic counters and core PHP helpers instead of Faker formatters because the bundled generator ships without the legacy providers; mirror the counter-driven identifiers and static address data when crafting bespoke fixtures so CI environments stay stable.
- Bulk customer seeding mirrors this determinism by issuing sequential `Customer 00001` style names and `customer#####@example.com` emails while keeping paired shipping/billing addresses in sync; reuse the seeded format when crafting manual fixtures so tests that assert on the convention stay reliable.【F:database/seeders/BulkCustomerSeeder.php†L28-L75】【F:database/factories/UserFactory.php†L31-L115】
- Filament table actions that rely on modal forms now require Livewire tests to mount the action, populate the form state, and then execute the mounted action. Use the `mountTableAction`/`setTableActionData` helpers (or their bulk equivalents) instead of calling `callTableAction` with raw data so validation hooks receive the intended payload.
- When table actions hide themselves on a per-record basis, retrieve the action from `$component->instance()->getTable()->getAction('action_name')`, bind the target record with `$action->record($record)`, and assert `isVisible()` directly. Filament's `callTableAction()` helper short-circuits hidden actions before mounting them, so visibility checks need to interrogate the action instance first.
- System setting resource coverage now verifies cache-clearing and export table actions end-to-end by inspecting the Livewire return payload and invoking the underlying action directly to assert the JSON response and attachment headers. 【F:tests/Feature/SystemResourceTest.php†L300-L369】
- Inline category creation inside the System resource form now persists array payloads from Livewire tests through a dedicated state cast and shared helper, ensuring nested category data resolves into IDs even when the `ActiveScope` hides inactive selections. 【F:app/Filament/Resources/SystemResource.php†L140-L217】
   - When Filament resources are guarded by policies, seed the corresponding Spatie permissions before booting the Livewire component so authorization checks pass deterministically during tests (see `tests/Admin/CustomerResourceTest.php`).【F:tests/Admin/CustomerResourceTest.php†L21-L52】【F:tests/Admin/CustomerResourceTest.php†L337-L360】
- The SEO data resource form now hydrates related model selects via capped search queries; avoid reintroducing eager `pluck()`/`preload()` calls, otherwise Livewire-powered tests will exhaust memory when large catalogues are present.【F:app/Filament/Resources/SeoDataResource.php†L70-L214】
- The analytics dashboard coverage in `tests/Feature/AnalyticsResourceTest.php` now asserts enum-aware status badges and the month-based grouping helpers on `App\Filament\Resources\AnalyticsResource`; keep the resource targeting the `Order` model and preserve the custom grouping closures when refactoring.【F:tests/Feature/AnalyticsResourceTest.php†L20-L276】【F:app/Filament/Resources/AnalyticsResource.php†L25-L215】
- Filament resources that previously lacked smoke coverage are now consolidated in `tests/Feature/Filament/Resources/MissingFilamentResourceCoverageTest.php`; update the data provider when introducing new admin resources so the shared Livewire list assertions stay comprehensive.【F:tests/Feature/Filament/Resources/MissingFilamentResourceCoverageTest.php†L7-L205】
- API route registration now stays under guard thanks to `tests/Feature/Api/ApiRouteCoverageTest.php`; extend the data provider whenever new endpoints land so unexpected URI or method changes surface as failing assertions rather than silent regressions.【F:tests/Feature/Api/ApiRouteCoverageTest.php†L7-L132】
- System setting category resources now ship with dedicated Filament v4 feature tests under `tests/Feature/Filament/Resources/SystemSettingCategoryResourceTest.php` and `tests/Feature/Filament/Resources/SystemSettingCategoryTranslationResourceTest.php`. The suites exercise table filters, duplication workflows, and bulk locale duplication helpers, so mirror their setup (seeding roles and resolving the admin panel) when expanding coverage for adjacent resources.【F:tests/Feature/Filament/Resources/SystemSettingCategoryResourceTest.php†L1-L154】【F:tests/Feature/Filament/Resources/SystemSettingCategoryTranslationResourceTest.php†L1-L181】
- Query performance checks in `tests/Performance/QueryPerformanceTest.php` now execute the underlying query builders directly instead of issuing HTTP requests. Keep the dataset lean (for example, limit attribute values to half a dozen records) and reset the array cache with `Cache::clear()` before repository assertions so cached payloads do not hide extra queries. When updating thresholds, measure the query count with the same eager-loading configuration to avoid masking genuine regressions.【F:tests/Performance/QueryPerformanceTest.php†L1-L208】
9. When overriding query builders (for example, removing global scopes in a Filament resource to expose soft-deleted or hidden rows), document the intent with an `@return Builder<Model>` annotation so PHPStan retains its generic context and other engineers remember why moderation tools bypass the storefront defaults.
   - The `PostApproval` relationship now calls `withoutGlobalScopes()` to ensure moderation history links back to posts that are still drafts or archived while `Tests\Models\PostApprovalTest` verifies relationship hydration across unpublished content.【F:app/Models/PostApproval.php†L34-L50】【F:tests/Models/PostApprovalTest.php†L32-L61】
10. Global scopes now re-validate their cached schema metadata after migrations complete, so if a model suddenly surfaces unexpected rows (for example, `CustomerGroup::enabled()` returning disabled fixtures) rerun the test after the migration phase to let the refreshed cache take effect rather than patching around stale `is_active`/`is_enabled` filters.
    - `Partner::getEffectiveDiscountRateAttribute()` and its commission twin now bypass the active/enabled scopes when lazily resolving a tier so model specs that null out the partner rate still inherit the historical tier values even if factories create a disabled tier record.【F:app/Models/Partner.php†L161-L207】
    - `PartnerTier` model specs should explicitly set `is_enabled = true` when verifying alphabetical ordering so the `ActiveScope` global constraint keeps the sample fixtures visible during assertions, mirroring the behaviour in `Tests\Models\PartnerTierTest`.
11. Customer group activation toggles still mirror `false` assignments across both `is_active` and `is_enabled` when only one value is provided, but the mutators now respect explicit divergence when both flags are supplied (for example `is_enabled = true` with `is_active = false`). The setters now inspect the dirty state to decide whether a value was explicitly provided in the same payload, so update any bespoke fixtures that rely on the previous always-cascade behaviour.【F:app/Models/CustomerGroup.php†L545-L612】
12. The SQLite fallback schema used by `Tests\\Support\\TestingDatabase` now provisions lightweight `products` and `product_images` tables whenever the full migration stack cannot execute. This keeps the product catalogue factories and `ProductImage` unit coverage operational even when the harness regenerates an abbreviated database, so include those columns in new assertions when expanding the suite.
13. XML catalogue imports now seed default `status` and `published_at` values so `Product` records remain discoverable through the `ActiveScope` and `PublishedScope` filters during regression runs, preventing soft failures when round-tripping fixture data via `XmlCatalogService`.【F:app/Services/XmlCatalogService.php†L382-L391】
14. Product discount accessors disregard sale prices that fail to undercut the comparison baseline, ensuring factory overrides that only tweak `price` continue to yield deterministic percentage discounts in regression tests.【F:app/Models/Product.php†L1806-L1836】

## System setting attribution safety

- Unit tests that spin up `SystemSetting` records should temporarily blank the attribution configuration keys (`attribution.system_user_id`, `attribution.system_user_email`, and `attribution.system_user_name`).
- The observer registered in `App\Providers\AppServiceProvider` backfills these columns with a “system” account, and without the override SQLite raises foreign key errors because no such user exists inside the in-memory harness.
- Applying the config override in the test `setUp()` keeps the observer idle while still exercising the same evaluation logic the production code relies on.
- `Tests\\Unit\\SystemSettingDependencyConditionTest` now centralises helper methods (`createSystemSetting`, `createDependency`) that call `createQuietly()` and eagerly load relations, so reuse them when expanding the suite to avoid repeating observer workarounds.
- Feature flag unit coverage follows a similar pattern: seed an attributed user (or clear the attribution config) before creating `FeatureFlag` factories so the observer can satisfy the `created_by` foreign key on SQLite.

## Schema guard rails for SQLite migrations

- The `2025_02_01_000002` system setting dependency migration now checks for existing columns and drops the legacy `system_setting_dependencies_condition_index` before removing the original JSON payload so `TestingDatabase::migrate()` can run repeatedly without tripping duplicate-column or missing-index errors on SQLite.
- The `2025_09_09_000000` discounts migration conditionally attaches the `zone_id` foreign key only when the zones table already exists, falling back to an index otherwise, which keeps early schema runs from failing before the zone migrations execute.
- Recommendation analytics snapshots now coerce the persisted `date` column into an ISO `Y-m-d` string during mutation so SQLite-backed `assertDatabaseHas` checks no longer trip over `00:00:00` suffixes while still returning Carbon instances at runtime.【F:app/Models/RecommendationAnalytics.php†L8-L26】【F:app/Models/RecommendationAnalytics.php†L41-L54】

## Dashboard Fixture Placeholders

- Historical dashboards now surface `Tests\Feature\DashboardFixtureTest` identifiers, with
  the source data continuing to ship through the JSON fixtures consumed by the test results
  feature tests. Maintain the mocked payloads in `tests/Feature/TestResults*` so the
  expected identifiers remain visible even though the dedicated feature test class has been
  retired. Continue keeping the remaining placeholder files in `tests/Unit`,
  `tests/Livewire`, `tests/Filament`, and `tests/Http` green so the progress reports stay
  consistent.
- A complementary Livewire regression in `tests/Feature/Livewire/Components/TestResultsComponentTest.php`
  now verifies that the widget renders detailed failure output and gracefully falls back to
  the `no_data` state when the JSON snapshot disappears, so align any fixture tweaks with the
  expected array structure for `tests` and `errors` entries to keep the assertions stable.

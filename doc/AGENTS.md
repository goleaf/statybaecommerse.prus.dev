# Project Automation & Filament Standards

This repository mixes Laravel 12 and Filament v4 code. Use the following guardrails whenever you touch files within this repo (scope: entire project).

## Quick orientation

- **Primary domains**: construction-product catalogues, marketing/news content, recommendation & referral systems, and multilingual storefront flows.
- **Key directories**: `app/Filament` (admin resources/widgets), `app/Services` (business logic), `app/Data` (DTOs), `database/seeders` (multilingual fixtures), `resources/views` (Blade + Volt storefront), and `scripts/*.mjs` (Playwright/e2e diagnostics).
- **Getting unstuck**: skim `README.md`, `docs/ARCHITECTURE_OVERVIEW.md`, and `docs/INDEX.md` for entry points; queue and cache nuances live in `docs/runbooks/CachePolicy.md`.

## 2025 baseline after merged PRs

- Treat the repository as feature-complete: the closure artefacts in `docs/analysis/PROJECT_CLOSURE_DOCUMENT.md`, `docs/analysis/COMPLETE_PROJECT_ARCHIVE_INDEX.md`, and the roll-up status in `memory-bank/progress.md` reflect the fully delivered Laravel + Filament programme.
- All Filament resources now follow the Schema-based APIs introduced in the compatibility PRs. Any reintroduction of legacy `Form` classes, mismatched method signatures, or navigation property types should be considered regressions and blocked in review.
- Enum management, discount engines, legal content, and advanced merchandising panels are online. When adding to these domains, keep logic in their dedicated namespaces (`app/Enums/**`, `app/Services/Discounts/**`, `app/Filament/Resources/Legal/**`, `app/Filament/Resources/Collection*`) and extend existing policies/tests instead of duplicating behaviour.
- The storefront Livewire flows (cart, checkout, recommendations) ship with production-ready translations and SEO defaults. Preserve LT/EN parity by updating both `resources/lang/lt` and `resources/lang/en` whenever adding copy.
- PR automation is configured through Release Please (`npm run release:pr`). Respect the generated versioning notes and avoid force-pushing rewritten histories; follow-up work should layer on top of the closed programme.

## Domain reference map

- **Settings & System Configuration**: models such as `app/Models/SystemSetting*.php`, service logic in `app/Services/SystemSettingsService.php`, seeded via `database/seeders/SystemSetting*.php`, and panels in `app/Filament/Resources/SystemSetting*` plus `app/Filament/Resources/NormalSetting*`.
  - When touching `SystemSettingDependency`, keep the canonical relation method named `dependsOnSettingRelation()` so query scopes can continue exposing the legacy `dependsOnSetting` builder macro without colliding with the relationship method name.
- **Catalog & Merchandising**: core entities `app/Models/Product.php`, `app/Models/ProductVariant.php`, `app/Models/Brand.php`, `app/Models/Category.php`, supporting data transfer objects like `app/Data/ProductRequestData.php` and `app/Data/CatalogIntegrityReport.php`, importer/exporters under `app/Services/ImportExport/**`, pricing helpers in `app/Services/Pricing/**`, and admin resources like `app/Filament/Resources/Product*` and `app/Filament/Resources/Collection*`.
  - Price list scheduling now relies on non-null `starts_at` timestamps so active scopes and helper methods agree on what "currently active" means; add backfills before toggling availability flags on legacy datasets.【F:database/migrations/2025_11_07_000001_add_schedule_columns_to_price_lists_table.php†L1-L47】【F:app/Models/PriceList.php†L104-L143】
  - Individual `Price` queries no longer depend on global `EnabledScope`/`DateRangeScope`; use the model's `enabled()` and `active()` scopes (or config tweaks) when filtering so reporting flows can inspect archived rows without opt-outs.【F:app/Models/Price.php†L95-L142】【F:config/model-scopes.php†L17-L86】
  - The `/api/categories/tree` endpoint reserves the literal `tree` slug via a regex constraint on the show route so cached tree responses win over implicit model binding; keep that guardrail intact when adjusting category routing.【F:routes/web.php†L540-L546】
- When filtering variant combinations, scope queries with `byProduct()` before `byCombination()` so the deterministic hash path activates and avoids supersets from JSON payloads.【F:app/Models/VariantCombination.php†L236-L276】
- Price listings now expose a locale-aware `orderedByName()` scope that left-joins translations and should be reused for alphabetic admin grids; the model also exposes a guarded `product()` relation that filters morph aliases to genuine products before hydrating relations.【F:app/Models/Price.php†L80-L126】
- **Commerce & Orders**: order aggregates in `app/Models/Order*.php`, cart and checkout services under `app/Services/Cart/**` and `app/Services/Payments/**`, Livewire flows within `app/Livewire/Pages/Checkout*.php`, and Filament management screens in `app/Filament/Resources/Order*`.
- **Customers & Reviews**: domain models `app/Models/Customer*.php`, `app/Models/Review*.php`, recommendation utilities in `app/Services/Recommendations/**`, customer dashboards in `app/Livewire/Pages/Account/**`, and Filament resources grouped beneath `app/Filament/Resources/Customer*` and `app/Filament/Resources/Review*`.
- **Content & Marketing**: articles and sliders under `app/Models/News*.php` and `app/Models/Slider*.php`, promotion tooling inside `app/Services/Discounts/**`, email automation in `app/Services/EmailMarketingService.php`, seeded fixtures in `database/seeders/News*`, and Filament resources such as `app/Filament/Resources/News*`, `app/Filament/Resources/Slider*`, and `app/Filament/Resources/Campaign*`.
  - When working with news categories, ensure migrations continue to target the canonical `news_category_translations` table and preserve `description` columns on both the base and translation tables so the HasTranslations trait resolves attributes without SQLite schema drift.【F:database/migrations/2025_09_10_001100_create_news_categories_tables.php†L32-L54】【F:database/migrations/2025_10_30_120000_fix_news_category_columns.php†L9-L45】
- **Automation & Analytics**: background orchestration in `app/Console/Commands/**`, event/listener wiring under `app/Listeners/**`, scheduled exports inside `app/Services/Export/**`, diagnostics in `app/Services/Debug/**`, analytics widgets/resources at `app/Filament/Resources/Analytics*`, and telemetry capture in `app/Models/Analytics*.php`.
- **Inventory & Filtering**: storefront listings handled by `app/Http/Controllers/InventoryController.php` now route all stock-status, brand, category, search, and sorting concerns through dedicated helper methods and sanitise incoming strings before touching the query builder.
  - Partner integrations consume the condensed inventory summary exposed by `App\Http\Controllers\Api\Partner\InventoryController`; keep the grouped `summary`, `low_stock`, and `out_of_stock` payload structure intact so downstream fulfilment dashboards continue to hydrate without additional pagination requests.【F:app/Http/Controllers/Api/Partner/InventoryController.php†L19-L164】【F:doc/api/contracts/PARTNER_API.md†L33-L103】

## Workflow Expectations

- **Custom automation notes**: Downstream maintainers rely on every code change containing meaningful inline commentary. When
  editing PHP, Blade, TypeScript, or configuration files, accompany new or modified logic with concise comments that explain
  intent, edge cases, or cross-module dependencies so reviewers can map behaviour quickly.
- **Moderation scope awareness**: Post approval relationships intentionally bypass global scopes so draft and archived posts remain reachable from the moderation log; preserve that behaviour when extending review tooling or adding new assertions.
- **Binary artefact policy**: Keep Git history free from generated binaries (fonts, compiled assets, archives). If a task
  requires producing such files for local validation, clean them up before committing so the repository stays source-only.
- **Documentation diligence**: After completing fixes or features, review the contents of `docs/` for any references that need
  refreshing (runbooks, audits, closure notes). Update the relevant markdown files to mirror the behaviour you introduced and
  extend agent instructions when new recurring rules emerge.
- **Example test placeholders**: The QA dashboards still read legacy `ExampleTest` identifiers, but the feature-level
  placeholder now lives solely inside the mocked JSON fixtures that populate `tests/Feature/TestResults*` suites.
  Keep the remaining example tests across `tests/Unit`, `tests/Livewire`, `tests/Filament`, and `tests/Http` intact and
  ensure the mocked payloads that expose `Tests\Feature\ExampleTest::*` remain in sync so the monitoring scripts continue
  to render the expected entries without the physical feature test file.
- **Model Pest harness**: When contributing to `tests/Models`, remember to import `Tests\\TestCase` and register it via
  `uses(TestCase::class);` so the shared SQLite testing harness initialises before factories run.

- After editing PHP or Blade files, run the quick quality loop before committing:
  1. `php -l <file>` to ensure syntax is valid.
  2. `vendor/bin/pint <file>` to fix style issues (run without `--test` so it can auto-fix).
  3. `vendor/bin/phpstan analyse <file> -c phpstan.neon --memory-limit=1G --no-progress` for static analysis.
  4. `vendor/bin/rector process <file> --ansi --no-progress-bar` to apply automated refactors where appropriate.
- When adding PHPUnit metadata such as `@covers`, use the native PHP attribute equivalents (for example `#[CoversClass]` or `#[CoversNothing]`) so the suite stays compatible with PHPUnit 12+ now that docblock metadata has been deprecated.
- When Blade templates change, refresh the cache with `php artisan view:clear` followed by `php artisan view:cache`.
- If changes touch application logic, run the most relevant `php artisan test --filter=...` target (or the full test suite when in doubt).
- When configuration or routes change, refresh caches via:
  ```bash
  php artisan config:clear && php artisan route:clear
  php artisan config:cache && php artisan route:cache
  ```
- Prefer documenting any outstanding warnings or skipped checks directly in commit messages or PR descriptions.
- When tuning analytics or stats queries, mirror the schema-aware fallbacks found in `App\Support\Stats\Series\ProductSeries` so dashboards continue to work for installs where denormalised totals or payment metadata are not yet available.

## Filament v4 Resource Rules

For every file under `app/Filament/**`:

- Import `Filament\Forms\Form` and `Filament\Tables\Table` when the resource defines `form()` or `table()`.
- Remove any `use App\Filament\Resources\Schema;` import—it conflicts with Filament v4 conventions.
- Ensure the method signatures match v4 expectations:
  ```php
  public static function form(Form $form): Form
  public static function table(Table $table): Table
  ```
- Inside `form()` bodies, use the `$form` variable consistently after updating the signature.
- Standardize the `$navigationIcon` property to be untyped with the docblock:
  ```php
  /** @var string|\BackedEnum|null */
  protected static $navigationIcon = 'heroicon-o-document-text';
  ```
  (Preserve the existing icon value when normalizing the property.)
- If a class named `App\Filament\Resources\Schema` exists, rename it to avoid collisions and update any imports accordingly.

## Testing & Data Discipline

- Keep database seeds multilingual (Lithuanian default, with English equivalents) and ensure currency formatting uses euros via `Number::currency` when rendering monetary values.
- When you add or modify Filament resources or Livewire components, provide or update Pest tests that cover CRUD operations, authorization, and localization behavior.
- Use factories with descriptive data to keep tests clear.
- When extending the `UserProductInteraction` regression coverage, rely on the shared helper methods inside the feature and unit test suites so new assertions inherit the consistent baseline fixtures without additional setup.【F:tests/Feature/UserProductInteractionTest.php†L16-L47】【F:tests/Unit/UserProductInteractionTest.php†L16-L33】
- Treat feature toggles as environment-aware: `FeatureToggleService` now reuses a base query and explicitly orders results so scoped flags win over global defaults. Preserve that structure when extending flag evaluation logic so staging-only rollouts remain reliable.【F:app/Services/FeatureToggleService.php†L57-L83】
- When tests disable framework features (for example setting `config('activitylog.enabled', false)`), capture the existing value and restore it in `tearDown()`/`afterEach` to avoid leaking state into subsequent suites that assert on activity log behaviour.
- Shipping eligibility unit tests now rely on `Country::updateOrCreate()` to reuse Baltic fixtures; mirror that approach when adding resolver coverage so seeder profiles that pre-populate `countries` records do not trigger unique `cca2` collisions.
- Customer group activation tests expect the model mutators to respect explicit divergence between `is_enabled` and `is_active`; only single-flag payloads cascade `false` assignments across both columns now, and the setters consult the dirty state to detect whether a counterpart flag was explicitly set in the same payload. Seed data that keeps groups enabled while marking them inactive should persist without additional overrides.【F:app/Models/CustomerGroup.php†L545-L612】

## Coding Standards

- Follow PSR-12, enable `declare(strict_types=1);`, and lean on constructor property promotion, typed properties/returns, and Laravel helpers where it improves clarity.
- Favor Tailwind utility classes in Blade templates and keep business logic inside view models or dedicated classes instead of views.
- Stick to SOLID design principles: encapsulate domain logic in services/repositories as needed, and avoid duplicating logic across the admin/front-end layers.
- Prefer the shared `resources/views/components/buttons/*.blade.php` components (e.g., `x-buttons.primary`) when rendering buttons in Blade templates so aliases stay consistent across authentication and storefront flows.【F:resources/views/components/buttons/primary.blade.php†L1-L26】

## Tooling Notes

- Use `php artisan boost:mcp` if you need to access the internal MCP utilities for searching docs or running targeted fixes.
- Keep change descriptions focused and concise; avoid generating additional documentation files unless requested.
- Git operations (add/commit/push) are allowed unless a higher-priority instruction forbids them.

## User Behaviour Reference

- When working on preference or interaction tracking, review `docs/analysis/USER_BEHAVIOR_MODELS_UPDATE.md` for the latest aliasing and metadata guidelines introduced after the regression fixes.
- When seeding multiple `UserPreference` records (especially in tests), vary both the type and key so the composite `(user_id, preference_type, preference_key)` index is respected; follow the ordering pattern in `tests/Unit/UserPreferenceModelTest.php` when asserting minimum-score scopes to avoid SQLite iteration quirks.
- When persisting `UserPreference` metadata payloads, ensure arrays are JSON-encoded before hitting the database; the model mutators now enforce this for consistency across SQLite and MySQL, so future changes should preserve that contract.

## Memory Bank Utilities

- The `memory-bank/` directory contains support files (`tasks.md`, `activeContext.md`, etc.). Ensure these remain consistent; do not delete or repurpose them without stakeholder approval.


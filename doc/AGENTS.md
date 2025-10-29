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
- **Catalog & Merchandising**: core entities `app/Models/Product.php`, `app/Models/ProductVariant.php`, `app/Models/Brand.php`, `app/Models/Category.php`, supporting data transfer objects like `app/Data/ProductRequestData.php` and `app/Data/CatalogIntegrityReport.php`, importer/exporters under `app/Services/ImportExport/**`, pricing helpers in `app/Services/Pricing/**`, and admin resources like `app/Filament/Resources/Product*` and `app/Filament/Resources/Collection*`.
- **Commerce & Orders**: order aggregates in `app/Models/Order*.php`, cart and checkout services under `app/Services/Cart/**` and `app/Services/Payments/**`, Livewire flows within `app/Livewire/Pages/Checkout*.php`, and Filament management screens in `app/Filament/Resources/Order*`.
- **Customers & Reviews**: domain models `app/Models/Customer*.php`, `app/Models/Review*.php`, recommendation utilities in `app/Services/Recommendations/**`, customer dashboards in `app/Livewire/Pages/Account/**`, and Filament resources grouped beneath `app/Filament/Resources/Customer*` and `app/Filament/Resources/Review*`.
- **Content & Marketing**: articles and sliders under `app/Models/News*.php` and `app/Models/Slider*.php`, promotion tooling inside `app/Services/Discounts/**`, email automation in `app/Services/EmailMarketingService.php`, seeded fixtures in `database/seeders/News*`, and Filament resources such as `app/Filament/Resources/News*`, `app/Filament/Resources/Slider*`, and `app/Filament/Resources/Campaign*`.
- **Automation & Analytics**: background orchestration in `app/Console/Commands/**`, event/listener wiring under `app/Listeners/**`, scheduled exports inside `app/Services/Export/**`, diagnostics in `app/Services/Debug/**`, analytics widgets/resources at `app/Filament/Resources/Analytics*`, and telemetry capture in `app/Models/Analytics*.php`.
- **Inventory & Filtering**: storefront listings handled by `app/Http/Controllers/InventoryController.php` now route all stock-status, brand, category, search, and sorting concerns through dedicated helper methods and sanitise incoming strings before touching the query builder.

## Workflow Expectations

- **Custom automation notes**: Downstream maintainers rely on every code change containing meaningful inline commentary. When
  editing PHP, Blade, TypeScript, or configuration files, accompany new or modified logic with concise comments that explain
  intent, edge cases, or cross-module dependencies so reviewers can map behaviour quickly.
- **Binary artefact policy**: Keep Git history free from generated binaries (fonts, compiled assets, archives). If a task
  requires producing such files for local validation, clean them up before committing so the repository stays source-only.
- **Documentation diligence**: After completing fixes or features, review the contents of `docs/` for any references that need
  refreshing (runbooks, audits, closure notes). Update the relevant markdown files to mirror the behaviour you introduced and
  extend agent instructions when new recurring rules emerge.

- After editing PHP or Blade files, run the quick quality loop before committing:
  1. `php -l <file>` to ensure syntax is valid.
  2. `vendor/bin/pint <file>` to fix style issues (run without `--test` so it can auto-fix).
  3. `vendor/bin/phpstan analyse <file> -c phpstan.neon --memory-limit=1G --no-progress` for static analysis.
  4. `vendor/bin/rector process <file> --ansi --no-progress-bar` to apply automated refactors where appropriate.
- When Blade templates change, refresh the cache with `php artisan view:clear` followed by `php artisan view:cache`.
- If changes touch application logic, run the most relevant `php artisan test --filter=...` target (or the full test suite when in doubt).
- When configuration or routes change, refresh caches via:
  ```bash
  php artisan config:clear && php artisan route:clear
  php artisan config:cache && php artisan route:cache
  ```
- Prefer documenting any outstanding warnings or skipped checks directly in commit messages or PR descriptions.

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

## Coding Standards

- Follow PSR-12, enable `declare(strict_types=1);`, and lean on constructor property promotion, typed properties/returns, and Laravel helpers where it improves clarity.
- Favor Tailwind utility classes in Blade templates and keep business logic inside view models or dedicated classes instead of views.
- Stick to SOLID design principles: encapsulate domain logic in services/repositories as needed, and avoid duplicating logic across the admin/front-end layers.
- Prefer the shared `resources/views/components/buttons/*.blade.php` components (e.g., `x-buttons.primary`) when rendering buttons in Blade templates so aliases stay consistent across authentication and storefront flows.【F:resources/views/components/buttons/primary.blade.php†L1-L26】

## Tooling Notes

- Use `php artisan boost:mcp` if you need to access the internal MCP utilities for searching docs or running targeted fixes.
- Keep change descriptions focused and concise; avoid generating additional documentation files unless requested.
- Git operations (add/commit/push) are allowed unless a higher-priority instruction forbids them.

## Memory Bank Utilities

- The `memory-bank/` directory contains support files (`tasks.md`, `activeContext.md`, etc.). Ensure these remain consistent; do not delete or repurpose them without stakeholder approval.


# Translation System Audit

## Summary
- The storefront and admin experiences share a multilayered translation stack that combines Laravel's locale middleware, Spatie-driven model tables, JSON and PHP language files, and database-backed UI key/value pairs so every interface has localized fallbacks.
- Filament-specific tooling (Language Tabs and the LaraZeus Spatie Translatable plugin) keeps admin forms in sync with locale requirements while helper services normalise supported locales across the application.
- The system ships with automated seeding, indexing, and migration scripts that enforce per-locale uniqueness, cascade deletes, and data backfills to prevent drift between base columns and translation tables.

## Database Translations
- Domain models opt into a reusable `HasTranslations` trait that exposes relation loaders, locale fallbacks, and attribute resolution backed by dedicated translation Eloquent models.【F:app/Traits/HasTranslations.php†L15-L92】【F:app/Models/Translations/ProductTranslation.php†L1-L120】
- Translation tables enforce locale uniqueness and cascade deletes (for example, product variant translations) while migration scripts backfill legacy columns into new per-locale rows to avoid data loss.【F:database/migrations/2025_11_06_130000_create_product_variant_translations_table.php†L13-L115】
- `UiTranslation` provides a centralised repository for admin/UI keys with query scopes, metadata casting, and helper methods to fetch or set values with language fallbacks; the News seeder populates Lithuanian and English variants to guarantee parity.【F:app/Models/UiTranslation.php†L32-L127】【F:database/seeders/NewsTranslationSeeder.php†L10-L84】
- System setting category translations now persist locale-specific UI metadata through the new `meta` JSON column, keeping administrative tabs and storefront breadcrumbs aligned with structured payloads stored alongside translated copy.【F:database/migrations/2025_01_31_000003_add_meta_columns_to_system_setting_tables.php†L21-L35】

## JSON & PHP Language Files
- Storefront copy is surfaced through locale JSON dictionaries that mirror storefront flows (cart, checkout, analytics) to enable string-based lookups without namespace prefixes.【F:resources/lang/en.json†L1-L120】
- Fallback resolution leans on Laravel's conventional PHP language files when JSON lookups miss: the `TranslationService` normalises keys, retries namespaced variants, and finally inspects the root `<locale>.php` file with placeholder substitution before returning the original key.【F:app/Services/TranslationService.php†L29-L168】

## Laravel Translation Flow
- `SetLocale` middleware orchestrates locale selection by inspecting route parameters, query overrides, Accept-Language headers, persisted session/cookie values, and user preferences, then synchronises the resolved locale across the session, cookies, and response headers while respecting configured fallbacks and currency mapping.【F:app/Http/Middleware/SetLocale.php†L15-L134】
- Core configuration defines the default locale, fallback locale, and the comma-separated list of supported locales consumed throughout middleware and services, ensuring consistent locale discovery and URL generation.【F:config/app.php†L76-L110】
- Higher-level helpers such as `TranslationService` and `MultiLanguageTabService` expose shared locale lists (`getAvailableLocales` / `getAvailableLanguages`) so console commands, UI builders, and API responses rely on the same configuration surface.【F:app/Services/TranslationService.php†L83-L115】【F:app/Services/MultiLanguageTabService.php†L24-L156】

## Spatie Translatable Usage
- Models that rely on JSON column translation leverage `Spatie\Translatable\HasTranslations` alongside the custom `TranslatableRecord` contract to serialise attributes per locale while keeping default columns populated for the primary language.【F:app/Models/Product.php†L77-L191】【F:app/Models/User.php†L59-L66】
- The Filament admin panel registers the LaraZeus Spatie Translatable plugin with the configured locales, ensuring list, create, edit, and view pages honour the active language toggle across resources like Orders, Referrals, and Addresses.【F:app/Providers/Filament/AdminPanelProvider.php†L75-L145】【F:app/Filament/Resources/OrderResource.php†L65-L87】

## Filament Language Tabs
- Filament resource forms wrap text inputs in `LanguageTabs::make([...])` blocks so administrators can edit multi-locale payloads inline while keeping schema organisation intact (e.g., product forms).【F:app/Filament/Resources/ProductResource.php†L177-L255】
- Configuration exposes the default and required locales for the tabs, derived from shared localisation settings, preventing drift between admin tooling and the broader application stack.【F:config/filament-language-tabs.php†L1-L31】
- `MultiLanguageTabService` builds dynamic tab schemas, badges, and seed data for Filament components, automatically binding translated field inputs and rehydrating records when editing existing translations.【F:app/Services/MultiLanguageTabService.php†L24-L200】

## Observations & Opportunities
- Locale expansion requires aligning three sources: `config/app.php` (`supported_locales`), `config/filament-language-tabs.php` defaults, and any database seeders for `UiTranslation`; ensuring these change together will keep storefront, admin, and API layers coherent.【F:config/app.php†L76-L110】【F:config/filament-language-tabs.php†L1-L31】【F:database/seeders/NewsTranslationSeeder.php†L10-L84】
- Consider extending automated audits (see `I18nAuditCommand`) to validate parity between JSON storefront keys and database-backed UI translations so regressions are caught during CI rather than manual review.【F:app/Console/Commands/I18nAuditCommand.php†L345-L420】
- The testing harness now hardens the SQLite fallback by recreating system-setting translation tables when migrations are interrupted, ensuring localized factories and resource tests stay deterministic even in parallel runs.【F:tests/Support/TestingDatabase.php†L248-L303】【F:tests/Support/TestingDatabase.php†L562-L603】

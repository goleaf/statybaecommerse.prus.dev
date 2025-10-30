# Currency Features Audit

## Summary
| Capability | Status | Notes |
| --- | --- | --- |
| Default currency (EUR) | ✅ Implemented | Helper defaults, configuration, and seed data all enforce EUR as the baseline currency.
| Exchange rates – automatic updates | ⚠️ Partial | Manual rate refresh actions now sync via `CurrencyRateSyncService` using configured static rates, but no scheduler or external feed drives automatic updates.
| Currency conversion – real-time | ❌ Missing | System only formats stored amounts; no runtime conversion logic uses exchange rates.
| Price display – locale-aware formatting | ✅ Implemented | Helpers and views rely on `Number::currency` with locale-sensitive fallbacks, with regression tests.
| Decimal places – per-currency precision | ⚠️ Partial | `decimal_places` columns drive formatting, but the enum helper references an undefined KRW case.
| Symbol position – configurable | ✅ Implemented | `Currency::formatAmount` honours before/after placement when symbols are stored.

## Findings

### Default Currency (EUR)
- `current_currency()` caches a session override but ultimately falls back to `EUR`, ensuring deterministic behaviour even before settings are hydrated.【F:app/helpers.php†L67-L97】
- Shared configuration and application features both pin the default currency to EUR for localization and feature toggles.【F:config/shared.php†L36-L44】【F:config/app-features.php†L37-L55】
- Boot logic preloads the Number helper with the configured default currency, keeping formatting helpers aligned.【F:app/Providers/AppServiceProvider.php†L381-L386】
- Seed data provisions an EUR record marked as default alongside other enabled currencies so fresh installs inherit the baseline correctly.【F:database/seeders/CurrencySeeder.php†L14-L28】

### Exchange Rates – Automatic Updates
- Filament rate-update actions now delegate to `CurrencyRateSyncService`, which persists values returned by the static `CurrencyRateProvider`, ensuring manual refreshes mutate stored exchange data.【F:app/Filament/Resources/CurrencyResource.php†L326-L372】【F:app/Services/CurrencyRateSyncService.php†L18-L67】
- Static rate lookups can be overridden per environment via `config/currency.php`, and feature coverage now asserts both single-record and bulk refresh flows update the database instead of using placeholders.【F:config/currency.php†L1-L12】【F:tests/Feature/CurrencyResourceTest.php†L198-L234】
- No scheduled job or background service consumes the `auto_update_rate` flag, so enabling the toggle still has no operational effect beyond manual actions.【F:app/Filament/Resources/CurrencyResource.php†L213-L268】

### Currency Conversion – Real-time
- Currency formatting relies on helpers that wrap Laravel's `Number::currency` or basic number formatting; there is no conversion logic that applies exchange rates to transform values between currencies.【F:app/helpers.php†L151-L233】
- `Currency::formatAmount()` formats amounts exactly as provided and never references `exchange_rate`, so stored figures must already be in the target currency.【F:app/Models/Currency.php†L460-L475】
- The admin resource and factories capture exchange-rate metadata, but without downstream usage the platform cannot produce real-time conversions from this data.【F:app/Filament/Resources/CurrencyResource.php†L148-L217】【F:database/factories/CurrencyFactory.php†L15-L70】

### Price Display – Locale-aware Formatting
- Formatting helpers default to locale-aware `Number::currency` with graceful fallbacks, ensuring consistent separators and symbols even when PHP intl is unavailable.【F:app/helpers.php†L151-L211】
- Storefront components format prices through `Number::currency` using the current locale and currency, keeping customer-facing displays localized.【F:resources/views/components/product-card.blade.php†L161-L173】
- Pest tests exercise Lithuanian and English locales to confirm comma/dot separators and symbol usage remain correct across environments.【F:tests/models/core/CurrencyHelperTest.php†L7-L130】
- The Filament wishlist admin infolist deliberately formats the persisted `current_price` in the English locale so regression coverage can assert on the `€123.45` pattern without conflicting with Lithuanian storefront defaults.【F:app/Models/WishlistItem.php†L69-L75】【F:app/Filament/Resources/WishlistItemResource.php†L650-L676】

### Decimal Places – Per-currency Precision
- The `currencies` table stores `decimal_places` and related separators, and `Currency::formatAmount()` respects those settings when rendering values.【F:app/Models/Currency.php†L41-L58】【F:app/Models/Currency.php†L460-L475】
- `CurrencyEnum::getDecimalPlaces()` attempts to provide zero-decimal overrides, but it references an undefined `KRW` case, so calling it will raise an error and indicates the helper needs alignment with the enum definition.【F:app/Enums/CurrencyEnum.php†L82-L87】

### Variant Price Change Reporting
- Variant price history percentages are now rounded to two decimal places before being exposed to tables or exports, keeping UI badges consistent with regression suite expectations.【F:app/Models/VariantPriceHistory.php†L70-L116】

### Price List Scheduling Windows
- Legacy schemas that lacked scheduling metadata now receive nullable `starts_at`/`ends_at` timestamps so pricing tests and runtime helpers can evaluate temporal availability without skipping coverage.【F:database/migrations/2025_11_07_000001_add_schedule_columns_to_price_lists_table.php†L1-L47】
- Active price lists must declare a concrete `starts_at` value to be treated as live, mirroring the behaviour of the `isActive()` helper and avoiding indefinite records from leaking into "currently active" queries.【F:app/Models/PriceList.php†L104-L143】
- Individual price records now rely on explicit query scopes (`enabled()` and `active()`) instead of implicit globals so analysts can inspect disabled or expired entries without bypassing framework constraints.【F:app/Models/Price.php†L95-L142】【F:config/model-scopes.php†L17-L86】

### Symbol Position – Before/After Amount
- `Currency::formatAmount()` switches formatting based on the persisted `symbol_position`, supporting both prefix and suffix layouts depending on business requirements.【F:app/Models/Currency.php†L462-L475】

### Feature Toggle Cache Resilience
- `FeatureToggleService` now bakes the most recent feature flag `updated_at` timestamp into cache keys, ensuring evaluations refresh automatically after administrators create or update environment-scoped toggles.【F:app/Services/FeatureToggleService.php†L41-L87】
- Environment-scoped flags take precedence over global definitions thanks to explicit ordering, so staging rollouts are no longer masked by previously created production toggles.【F:app/Services/FeatureToggleService.php†L57-L83】
- Regression coverage confirms cached fallback results give way to persisted flags without manual cache clearing, preventing stale toggle states from leaking between test scenarios or queued jobs.【F:tests/Unit/Services/FeatureToggleServiceTest.php†L118-L141】
- The attribution observer only fills the `*_by_name` columns when the database exposes them as text fields, side-stepping legacy integer columns with foreign keys that would otherwise reject cached names in SQLite-backed test runs.【F:app/Observers/UserAttributionObserver.php†L27-L30】【F:app/Observers/UserAttributionObserver.php†L152-L184】

## Recommendations
- Wire an external exchange-rate provider and scheduler into the new synchroniser so the `auto_update_rate` toggle triggers background refreshes instead of relying solely on manual actions.
- Introduce a conversion helper/service that applies stored exchange rates for on-the-fly conversions, especially for presenting multi-currency prices when source data is only in EUR.
- Fix `CurrencyEnum::getDecimalPlaces()` by defining the KRW case (or removing it) and expanding coverage tests to catch enum drift in the future.

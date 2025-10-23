# Changelog

## [Unreleased]

### Added
- Added a standalone `BattleService` with deterministic casualty and loot calculations, complete with PHPUnit coverage to keep
  the Travian battle simulation behaviour reproducible for future tooling work.
- Enabled Laravel Sanctum API token support, including guard configuration, middleware aliases, and the published personal access token migration.
- Introduced dedicated rate limiters for the authenticated user endpoint and storefront checkout requests.
- Documented consistent JSON problem responses for the profile and checkout flows, falling back to user-owned carts when sessions rotate.
- Localized the new customer badge labels (`customers.badges.*`) and surfaced them as email verification and activity chips in the admin customer table.
- Localized the shared `common.timestamps` label for admin timestamp sections across the EN and LT translation files.
- Introduced document audit logging with localized admin infolists, API exposure, and persisted attribution metadata for auditable history.

### Dependencies
- Added `awcodes/filament-badgeable-column` (^3.0) to power reusable badge styling in Filament table columns while integrating the vendor Blade templates into our Tailwind build pipeline.
- Replaced wildcard constraints with caret ranges for `dutchcodingcompany/filament-socialite` (`^3.0`), `lara-zeus/bolt` (`^4.0.5`), and `pxlrbt/filament-excel` (`^3.1`) to resolve `composer validate` warnings.
- `composer why-not phpunit/phpunit 12.4.1` → `pestphp/pest` v4.1.2 conflicts with versions greater than 12.4.0; remains blocked.
- `composer why-not zircote/swagger-php 5.5.1` → the application requires `^4.9` and cannot adopt the 5.x breaking change.
- Verified production install flow with `composer install --no-dev --prefer-dist`.
- Raised the floor for `novadaemon/filament-combobox` to `^2.0.1` as part of the scheduled Filament maintenance sweep so we can ship the upstream dropdown focus bugfix in the next admin UI release.

### Changed
- Normalized Filament resources, pages, widgets, and relation managers to the v4 schema signatures, replacing legacy
  `Form|array`/`Table|array` return types so composer package discovery completes without fatal errors.
- Standardized `$navigationIcon` and `$navigationGroup` declarations to `\BackedEnum|string|null` and `\UnitEnum|string|null`
  respectively, keeping navigation metadata compatible with Filament's typed properties while documenting accepted values.
- Normalized storefront API product payloads to expose `main_image`/`thumbnail` attributes, updated Livewire autocomplete widgets to respect the new fields, and documented coverage with new feature tests.
- Documented the Collection Rule resource's Filament v4 form/table signatures, modal reorder workflow, and cache maintenance page alignment to guide future admin updates.
- Added translation-backed fallbacks for missing review ratings while aligning Filament navigation icon docblocks and shared navigation group helpers across the review, system setting category, and enhanced ecommerce widgets.
- Swapped Campaign resource schemas to Filament v4 `Section`/`Grid` components, refreshed table action imports, and synced the related translation manager plus Variant Analytics navigation icon docblock with v4 conventions.
- Refined the Variant Inventory resource form to rely on section column layouts instead of nested grids, improving readability while keeping bespoke spans for operational notes and ensuring importer select components remain organized.
- Restored typed navigation icons for the Menu, Enum Management, and Variant Analytics resources while cleaning up redundant `UnitEnum` imports in favor of fully qualified annotations.
- Shifted Category and Variant Analytics resources onto the Filament tables namespace helpers, introduced `Str`-based label formatting, and pared down the testing admin panel bootstrap to prevent missing table crashes during Pest runs.
- Routed the Menu activation toggle through Filament's success notification system to standardize feedback messaging.
- Added localized LaraZeus list group quick links to the product, order, customer, and post view record pages so relationship collections surface with consistent icons, tooltips, and storefront URLs.
- Grouped the Product History resource's custom Flatpickr helper with other `App\` imports so reviewers spot bespoke components faster during diffs.
- Modernized the CartItem administration surface by adopting Filament v4 form components, preventing non-persisted field dehydration, and wiring the model/migration updates that expose product metadata accessors alongside discount tracking.
- Updated the Variant Inventory resource to use the Filament v4 `Form` return type and refreshed the navigation icon annotations for consistency across admin resources.
- Recorded the shared Filament Number percentage helper adoption for Variant Inventory and Analytics resources so reviewers notice the locale-aware formatting upgrade.
- Brought the Wishlist Item resource's form and table methods in line with Filament v4 return types to prevent schema hydration regressions during admin CRUD operations.
- Clarified the Wishlist Item resource's navigation icon docblock so reviewers understand the Filament v4 sidebar metadata alignment.

### Fixed
- Restored the executable Husky shim after PR #687 overwrote it with the warning stub so Git hooks keep running during local workflows.
- Eliminated redundant null coalescing in the Variant Stock widget aggregation logic to resolve phpstan nullability reports.
- Reinstated the Husky shared bootstrap script so Git hooks run without deprecation notices or missing PATH exports during repository installs.
- Captured the `sh -n .husky/_/husky.sh` smoke check that verifies the restored script syntax before publishing commits.
- Removed the deprecated Husky v10 warning stub that overwrote the shim so local Git hooks keep executing through the repository toolchain instead of exiting early.
- Normalized the storefront JSON contracts, CSS widgets, and shared JavaScript bundles with Prettier so `npm run lint` succeeds without manual intervention in local setups.
- Updated the Address resource form/table signatures to the Filament v4 Schema API, removing package discovery fatals and keeping static analysis parsers satisfied.
- Matched the Activity Log resource navigation icon property type with Filament's union declaration to unblock composer script discovery and downstream tooling runs.
- Migrated the Admin User resource to Filament's Schema-based form/table signatures so composer autoload discovery and static analysis no longer encounter legacy Form return types.

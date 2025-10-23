# Changelog

## [Unreleased]

### Added
- Enabled Laravel Sanctum API token support, including guard configuration, middleware aliases, and the published personal access token migration.
- Introduced dedicated rate limiters for the authenticated user endpoint and storefront checkout requests.
- Documented consistent JSON problem responses for the profile and checkout flows, falling back to user-owned carts when sessions rotate.
- Localized the new customer badge labels (`customers.badges.*`) and surfaced them as email verification and activity chips in the admin customer table.

### Dependencies
- Added `awcodes/filament-badgeable-column` (^3.0) to power reusable badge styling in Filament table columns while integrating the vendor Blade templates into our Tailwind build pipeline.
- Replaced wildcard constraints with caret ranges for `dutchcodingcompany/filament-socialite` (`^3.0`), `lara-zeus/bolt` (`^4.0.5`), and `pxlrbt/filament-excel` (`^3.1`) to resolve `composer validate` warnings.
- `composer why-not phpunit/phpunit 12.4.1` → `pestphp/pest` v4.1.2 conflicts with versions greater than 12.4.0; remains blocked.
- `composer why-not zircote/swagger-php 5.5.1` → the application requires `^4.9` and cannot adopt the 5.x breaking change.
- Verified production install flow with `composer install --no-dev --prefer-dist`.
- Raised the floor for `novadaemon/filament-combobox` to `^2.0.1` as part of the scheduled Filament maintenance sweep so we can ship the upstream dropdown focus bugfix in the next admin UI release.

### Changed
- Swapped Campaign resource schemas to Filament v4 `Section`/`Grid` components, refreshed table action imports, and synced the related translation manager plus Variant Analytics navigation icon docblock with v4 conventions.
- Restored typed navigation icons for the Menu, Enum Management, and Variant Analytics resources while cleaning up redundant `UnitEnum` imports in favor of fully qualified annotations.
- Routed the Menu activation toggle through Filament's success notification system to standardize feedback messaging.
- Added localized LaraZeus list group quick links to the product, order, customer, and post view record pages so relationship collections surface with consistent icons, tooltips, and storefront URLs.
- Grouped the Product History resource's custom Flatpickr helper with other `App\` imports so reviewers spot bespoke components faster during diffs.
- Adopted the Filament `Section` helper for partner forms, registered the partner view route, finalized the partner view page class, refreshed the Variant Analytics navigation icon docblock, and expanded the navigation fixer script to deduplicate stray `UnitEnum` imports within Filament pages.

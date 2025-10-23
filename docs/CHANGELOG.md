# Changelog

## [Unreleased]

### Added
- Implemented the Campaign Product Target resource with a full CRUD form, marketing-focused table tooling, and a dedicated view page for auditing individual targets.
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
- Hardened the campaign product target schema migration with the columns used by the model/resource and guarded the legacy product image path normalization against missing columns.
- Converted Variant Analytics navigation icon handling to an explicit accessor and removed duplicate UnitEnum imports from custom Filament widgets to satisfy PHP 8.4 autoloading rules.
- Restored typed navigation icons for the Menu, Enum Management, and Variant Analytics resources while cleaning up redundant `UnitEnum` imports in favor of fully qualified annotations.
- Reworked variant inventory and analytics resources to embrace Filament v4 section column layouts while centralising percentage formatting helpers for consistent reporting.
- Routed the Menu activation toggle through Filament's success notification system to standardize feedback messaging.
- Added localized LaraZeus list group quick links to the product, order, customer, and post view record pages so relationship collections surface with consistent icons, tooltips, and storefront URLs.
- Grouped the Product History resource's custom Flatpickr helper with other `App\` imports so reviewers spot bespoke components faster during diffs.
- Adopted the Filament `Section` helper for partner forms, registered the partner view route, finalized the partner view page class, refreshed the Variant Analytics navigation icon docblock, and expanded the navigation fixer script to deduplicate stray `UnitEnum` imports within Filament pages.

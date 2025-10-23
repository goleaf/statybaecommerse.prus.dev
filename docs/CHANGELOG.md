# Changelog

## [Unreleased]

### Added
- Enabled Laravel Sanctum API token support, including guard configuration, middleware aliases, and the published personal access token migration.
- Introduced dedicated rate limiters for the authenticated user endpoint and storefront checkout requests.
- Documented consistent JSON problem responses for the profile and checkout flows, falling back to user-owned carts when sessions rotate.

### Dependencies
- Replaced wildcard constraints with caret ranges for `dutchcodingcompany/filament-socialite` (`^3.0`), `lara-zeus/bolt` (`^4.0.5`), and `pxlrbt/filament-excel` (`^3.1`) to resolve `composer validate` warnings.
- Upgraded `novadaemon/filament-combobox` to the `^2.0.1` constraint as part of the scheduled Filament maintenance sweep so we can consume the upstream dropdown focus bugfix ahead of the next admin UI release.
- `composer why-not phpunit/phpunit 12.4.1` → `pestphp/pest` v4.1.2 conflicts with versions greater than 12.4.0; remains blocked.
- `composer why-not zircote/swagger-php 5.5.1` → the application requires `^4.9` and cannot adopt the 5.x breaking change.
- Verified production install flow with `composer install --no-dev --prefer-dist`.
- Raised the floor for `novadaemon/filament-combobox` to `^2.0.1` to capture upstream fixes while staying within the 2.x release line.

### Changed
- Restored typed navigation icons for the Menu, Enum Management, and Variant Analytics resources while cleaning up redundant `UnitEnum` imports in favor of fully qualified annotations.
- Routed the Menu activation toggle through Filament's success notification system to standardize feedback messaging.
- Added localized LaraZeus list group quick links to the product, order, customer, and post view record pages so relationship collections surface with consistent icons, tooltips, and storefront URLs.
- Clarified Company size selection and filtering to support nullable records with explicit placeholders inside the Filament resource.

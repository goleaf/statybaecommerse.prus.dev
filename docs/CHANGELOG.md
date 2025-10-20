# Changelog

## [Unreleased]

### Dependencies
- Reviewed `phpunit/phpunit` and confirmed 12.4.1 is unavailable because `pestphp/pest` v4.1.2 conflicts with versions greater than 12.4.0 (`composer why-not phpunit/phpunit 12.4.1`).
- `composer validate` passes with existing warnings about wildcard constraints for `dutchcodingcompany/filament-socialite`, `lara-zeus/bolt`, and `pxlrbt/filament-excel`.
- Verified production install flow with `composer install --no-dev --prefer-dist --no-scripts`.

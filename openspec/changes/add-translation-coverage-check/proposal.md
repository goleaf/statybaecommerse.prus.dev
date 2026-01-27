# Change: Add translation coverage check

## Why
Missing or inconsistent translations in the app and Filament UI cause mixed-language screens and regressions.

## What Changes
- Add a translation coverage check that scans app and Filament code for translation keys
- Enforce consistent key format (snake_case, e.g. home_page) for app-owned translation keys
- Report missing keys across all locales in lang/ and fail the check

## Impact
- Affected specs: translation-coverage (new)
- Affected code: translation files in lang/, scan tooling, CI/test hooks

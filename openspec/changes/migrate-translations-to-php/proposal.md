# Change: Migrate translations to PHP-only language files

## Why
The project currently mixes JSON translation files, PHP translation files, and hardcoded strings. This makes localization inconsistent and hard to maintain. We need a single translation source and full coverage of user-facing strings.

## What Changes
- Migrate all keys and values from `lang/*.json` into locale PHP files under `lang/{locale}/`.
- Replace user-facing hardcoded strings in `app/` and `resources/` with translation keys.
- Remove JSON translation files and ensure lookups rely only on PHP translation files.

## Impact
- Affected specs: localization
- Affected code: `app/`, `resources/`, `lang/`

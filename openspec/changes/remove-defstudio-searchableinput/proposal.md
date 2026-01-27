# Change: Remove DefStudio SearchableInput dependency

## Why
The project currently references DefStudio SearchableInput classes that are not installed, causing runtime errors. We need a native replacement that preserves existing searchable input behavior without relying on the external package.

## What Changes
- Replace DefStudio SearchableInput usage with the in-project SearchableInput component and helpers.
- Remove DefStudio class references from service providers and search helpers.
- Ensure searchable input hydration and payload handling continue to work.

## Impact
- Affected specs: searchable-input
- Affected code: Filament form components and searchable input helpers

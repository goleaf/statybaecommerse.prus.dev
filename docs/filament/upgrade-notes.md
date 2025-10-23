# Filament Upgrade Notes

## Enum-aware Navigation Configuration

- Filament pages, resources, widgets, and relation managers now document `$navigationIcon` and `$navigationGroup` as accepting `string`, `\BackedEnum`, or `\UnitEnum` values via PHPDoc. This keeps the properties untyped while ensuring editors and static analysers understand enum-based navigation values.

## Strict Builder Return Signatures

- All Filament form and table builders now return concrete `Form` and `Table` instances. Each method includes an inline reminder that Filament 4 expects the builder instance, preventing regressions back to the deprecated array return style.

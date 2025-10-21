# Filament v4 Upgrade Notes

## Enum-friendly navigation metadata

- Resources that expose navigation icons or groups now type their `$navigationIcon` and `$navigationGroup` properties as `BackedEnum|string|null` and `UnitEnum|string|null` respectively. This keeps Filament 4 happy when you provide PHP enums like `NavigationGroup::System`.
- `getNavigationGroup()` implementations should continue returning strings, but they now coerce enum instances internally so translations remain consistent.

## Builder return types for forms, tables, and relation managers

- All Filament resources, pages, widgets, and relation managers now return the fluent `Form` or `Table` builders directly (`Form`/`Table` instead of `Form|array` / `Table|array`).
- When customising builders, keep chaining calls on the builder instance (e.g. `$form->schema([...])`) and return the builder so Filament 4 can apply middleware hooks correctly.
- Relation managers such as `OrdersRelationManager` include inline comments as reminders that the fluent builders must be returned, helping future contributors avoid reverting to array responses.

These adjustments align the admin panel with Filament 4's stricter typing expectations and prevent deprecation notices during upgrades.

# Export Column Contract

The export system is powered by dedicated `Exportable` classes that expose a consistent column contract. Each exportable defines a list of `ExportColumn` instances which provide the following information:

- `key` – unique identifier used to persist the column selection.
- `label` – human readable header rendered in the exported artifact.
- `attribute` or `resolver` – the attribute path (via `data_get`) or a closure that resolves the value from the Eloquent model.

When creating a new exportable:

1. Implement `App\Services\Export\Contracts\Exportable`.
2. Return the available columns from `columns()` using `ExportColumn` objects keyed by the column `key`.
3. Supply a sensible subset of `defaultColumns()` that will be used when the requester does not specify a selection.
4. Use the provided `map()` helper pattern to convert the model into row values.

Columns should always resolve to scalars or strings. Complex structures should be JSON encoded inside the resolver.

The Filament bulk export actions surface the column contract through a checkbox list, ensuring administrators can tailor the exported fields per request.

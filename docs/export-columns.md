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

## Export service configuration

All exports are orchestrated by `App\\Services\\Export\\ExportService`, which queues a `ProcessExport` job and streams rows through writer implementations. Runtime behaviour is governed via `config/export.php`:

- `disk` – filesystem disk used to store generated artifacts. Defaults to the application filesystem disk.
- `chunk_size` – number of rows fetched per query chunk while processing an export.
- `download_url_ttl` – minutes a signed download URL remains valid.
- `formats` – map of supported format keys to writer classes implementing `ExportWriter`.

Filament resources read from the same configuration to populate the format select list on their bulk actions, falling back to CSV when no custom formats are provided.

## Signed download endpoint

Completed exports can be downloaded through the signed route `api.exports.download`. The export service issues URLs via `downloadUrl()` using the configured TTL and the download controller streams the artifact from the configured disk while enforcing `ExportStatus::Completed`.

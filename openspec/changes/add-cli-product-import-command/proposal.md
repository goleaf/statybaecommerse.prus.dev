## Why
The application schedules `import:products` nightly, but no corresponding Artisan command exists. Teams also need a repeatable CLI import that reuses the admin product CSV pipeline while enforcing update-only behavior by existing SKU.

## What Changes
- Add a new Artisan command `import:products {path} {--chunk=100}`.
- Reuse the existing product import pipeline (`ProductImporter` + imports tables + `CsvImportProcessor`) for CSV parsing, mapping, and row processing.
- Run in sync mode by SKU and require an existing sync match in CLI mode so unmatched rows fail instead of creating products.
- Attribute CLI imports to the first admin user (`users.is_admin = 1`) and fail fast when none exists.
- Return success for partial imports (row failures) while printing warning summaries.

## Impact
- Enables the already-scheduled `import:products` task in nightly automation.
- Adds an auditable CLI import path without changing existing admin UI behavior.
- Prevents duplicate product creation during command-based sync imports.

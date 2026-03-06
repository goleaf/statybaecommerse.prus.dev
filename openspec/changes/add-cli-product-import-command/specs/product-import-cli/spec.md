## ADDED Requirements
### Requirement: Import Products from CLI Using Existing CSV Pipeline
The system MUST provide an Artisan command that imports products from CSV using the same product importer pipeline as the admin product CSV import.

#### Scenario: Command processes CSV through product importer
- **WHEN** a user runs `php artisan import:products {path}` with a readable CSV file
- **THEN** the system creates an import record and processes rows through `ProductImporter` and `CsvImportProcessor`

### Requirement: CLI Sync Mode Must Update Existing Products by SKU Only
The CLI product import command MUST run product sync in update-only mode using SKU as the sync key.

#### Scenario: Existing SKU is updated
- **GIVEN** a product already exists with SKU `ABC-123`
- **WHEN** a CSV row with SKU `ABC-123` is imported via CLI
- **THEN** the existing product is updated and no duplicate product is created

#### Scenario: Missing SKU match fails the row
- **GIVEN** no product exists for SKU `MISSING-001`
- **WHEN** a CSV row with SKU `MISSING-001` is imported via CLI
- **THEN** the row is marked failed and no new product is created

### Requirement: CLI Import Ownership Must Use Admin User
The CLI product import command MUST attribute imports to an existing admin user.

#### Scenario: No admin user exists
- **GIVEN** there are no users with `is_admin = 1`
- **WHEN** the command is executed
- **THEN** the command fails fast with a clear error and does not create an import record

### Requirement: Partial Row Failures Do Not Fail Command Exit
The CLI product import command MUST return a success exit code when processing completes, even if some rows fail.

#### Scenario: Command completes with row failures
- **WHEN** the command finishes and at least one row failed
- **THEN** it prints a warning summary and exits with code `0`

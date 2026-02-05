## CHANGED Requirements
### Requirement: Product Schema Excludes Deprecated Fields
The system MUST NOT include deprecated product fields in the schema.

#### Scenario: Fresh install schema
- **WHEN** the database schema is created from migrations
- **THEN** deprecated product columns are not present

### Requirement: Storefront Uses Published Visibility Rules
The system MUST determine storefront visibility using `is_enabled` and the published scope (`status` + `published_at`).

#### Scenario: Published product appears in storefront
- **GIVEN** a product is enabled and published
- **WHEN** storefront queries are executed
- **THEN** the product is eligible for display

### Requirement: Imports Reject Deprecated Columns
The system MUST fail validation when an import payload includes any removed product columns.

#### Scenario: CSV contains deprecated field
- **GIVEN** an import file includes a deprecated product column
- **WHEN** the import is validated
- **THEN** the import fails with a validation error

### Requirement: Translations Omit Summary
The system MUST NOT store product summaries in translation records.

#### Scenario: Saving translations
- **WHEN** product translations are saved
- **THEN** no summary field is persisted
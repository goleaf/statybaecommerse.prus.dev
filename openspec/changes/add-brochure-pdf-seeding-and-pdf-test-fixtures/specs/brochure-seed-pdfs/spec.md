## ADDED Requirements
### Requirement: Brochure Seeder Creates Downloadable PDFs
The system MUST provide a brochure seeder that creates brochure records and writes valid PDF files to secure media storage for each brochure file.

#### Scenario: Seed brochure PDFs
- **WHEN** `BrochureSeeder` is executed
- **THEN** brochure and brochure file records are created
- **AND** each brochure file path exists on secure storage as a valid PDF binary.

### Requirement: Brochure Seeder Is Profile-Integrated
The system MUST include brochure seeding in the default standard seeding profile used by optimized/full seed flows.

#### Scenario: Standard profile contains brochure seeder
- **WHEN** standard seeders are resolved from configuration
- **THEN** `BrochureSeeder` is included in the list.

### Requirement: Configurable Brochure Seed Volume
The system MUST support configurable brochure and file counts for seeded brochure PDFs.

#### Scenario: Override brochure seed counts
- **WHEN** brochure seeding config values are overridden
- **THEN** seeded brochure and brochure file totals match the configured values.

### Requirement: Shared Valid PDF Test Fixtures
The test suite MUST use shared valid PDF fixtures instead of ad-hoc raw PDF string literals in PDF-related tests.

#### Scenario: PDF fixture usage in tests
- **WHEN** PDF-dependent tests run
- **THEN** they consume shared fixture helpers that return valid PDF binaries.

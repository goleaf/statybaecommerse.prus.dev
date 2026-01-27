## ADDED Requirements
### Requirement: Translation Coverage Check
The system SHALL provide a project check that scans app-owned code and verifies that every referenced translation key exists in all locales under lang/.

#### Scenario: Missing translation key
- **WHEN** a translation key is referenced in app-owned code
- **AND** that key is missing from any locale under lang/
- **THEN** the check reports the missing key and fails

### Requirement: Translation Key Format
The system SHALL restrict app-owned translation keys to snake_case identifiers (e.g., home_page) and reject dot-notation or other formats.

#### Scenario: Invalid key format
- **WHEN** a translation key is referenced in app-owned code
- **AND** the key is not snake_case
- **THEN** the check reports the invalid key format and fails

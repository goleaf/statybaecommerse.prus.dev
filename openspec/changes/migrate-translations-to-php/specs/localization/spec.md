## ADDED Requirements
### Requirement: PHP-only translation sources
The system SHALL load translations exclusively from PHP files in `lang/{locale}/*.php` and SHALL NOT rely on JSON translation files.

#### Scenario: Translation lookup uses PHP files
- **WHEN** a translation key is resolved at runtime
- **THEN** the value is sourced from a PHP translation file
- **AND** JSON translation files are not required

### Requirement: User-facing strings use translation keys
The system SHALL use translation keys for user-facing strings in `app/` and `resources/`, with exceptions only for data values, identifiers, or non-localizable codes.

#### Scenario: UI labels are translatable
- **WHEN** a page or component renders user-facing labels
- **THEN** those labels are resolved through translation keys

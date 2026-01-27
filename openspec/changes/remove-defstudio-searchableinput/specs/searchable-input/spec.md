## ADDED Requirements
### Requirement: Searchable input component independence
The system SHALL provide searchable input fields in Filament forms without depending on the DefStudio SearchableInput package.

#### Scenario: Search results render without external dependency
- **WHEN** a user types into a searchable input field
- **THEN** the system returns matching results using the project search helpers
- **AND** no DefStudio classes are required at runtime

### Requirement: Searchable input state hydration
The system SHALL hydrate searchable input fields using the project search helpers and payload metadata.

#### Scenario: Existing searchable input selection persists
- **WHEN** a form with a searchable input is opened
- **THEN** the field is hydrated with the stored value and label using project helpers

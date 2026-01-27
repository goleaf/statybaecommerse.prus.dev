## REMOVED Requirements

### Requirement: Legacy Task Model
The system SHALL provide the `App\Models\Task` model and its task-specific relationships, scopes, and query helpers.

#### Scenario: Code references the legacy Task model
- **WHEN** application code attempts to resolve or instantiate `App\Models\Task`
- **THEN** the model is not available in the supported domain surface
- **AND** all in-repository references have been removed or refactored

### Requirement: Legacy Tag Model
The system SHALL provide the `App\Models\Tag` model and its polymorphic tag relationship APIs.

#### Scenario: Code references the legacy Tag model
- **WHEN** application code attempts to resolve or instantiate `App\Models\Tag`
- **THEN** the model is not available in the supported domain surface
- **AND** all in-repository references have been removed or refactored

### Requirement: Legacy Taggable Model
The system SHALL provide the `App\Models\Taggable` model as the polymorphic pivot between tags and taggable entities.

#### Scenario: Code references the legacy Taggable model
- **WHEN** application code attempts to resolve or instantiate `App\Models\Taggable`
- **THEN** the model is not available in the supported domain surface
- **AND** all in-repository references have been removed or refactored
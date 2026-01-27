## REMOVED Requirements
### Requirement: Legacy Modules and Relations
The system SHALL NOT include legacy models, architectural layers, and relations that reference them.

#### Scenario: Application boots without removed modules
- **WHEN** the application autoloads classes and registers service providers
- **THEN** no removed classes are referenced by container bindings, relations, routes, or configuration
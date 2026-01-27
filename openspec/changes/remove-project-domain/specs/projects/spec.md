## REMOVED Requirements

### Requirement: Projects Domain
The system SHALL provide a Project model backed by a projects table, and tasks SHALL belong to projects via project_id.

#### Scenario: Project domain removed
- WHEN the system is installed from scratch
- THEN the projects table is not created
- AND there is no App\Models\Project model
- AND tasks no longer reference project_id.
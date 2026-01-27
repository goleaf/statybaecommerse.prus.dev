## REMOVED Requirements

### Requirement: Persist Admin Activity Logs
The system SHALL persist high-level administrator audit records to the `admin_activity_logs` table.

#### Scenario: Admin action is recorded
- **WHEN** an admin-triggered action is executed
- **THEN** an `admin_activity_logs` record is created with actor, action, and context details

### Requirement: Expose Admin Activity Log relationships
The system SHALL expose admin activity log relationships on supported resources.

#### Scenario: Resource activity is accessed
- **WHEN** a resource is queried for related admin activity logs
- **THEN** a relationship to `AdminActivityLog` records is available
## REMOVED Requirements

### Requirement: Persist system logs
The system SHALL persist structured system logs in a dedicated `system_logs` table that can be queried via an Eloquent model.

#### Scenario: System log entries are stored
- **WHEN** the application records a system log entry
- **THEN** the entry is stored in the `system_logs` table
- **AND** the entry can be retrieved via `App\Models\SystemLog`

### Requirement: Create system logs schema on install
The system SHALL create the `system_logs` table during migrations for fresh environments.

#### Scenario: Fresh install runs migrations
- **WHEN** `php artisan migrate` is executed on a new database
- **THEN** the `system_logs` table is created

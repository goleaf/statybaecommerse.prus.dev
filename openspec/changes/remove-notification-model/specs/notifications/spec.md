## REMOVED Requirements

### Requirement: Custom Notification model
The system SHALL provide a custom `App\Models\Notification` model that extends Laravel's `DatabaseNotification` and exposes domain-specific scopes, computed attributes, and aggregate statistics helpers.

#### Scenario: Accessing custom notification APIs
- **WHEN** application code type-hints or imports `App\Models\Notification`
- **THEN** those APIs are available for querying notifications, computing stats, and presenting enriched fields

## ADDED Requirements

### Requirement: Notifications use Laravel defaults
The system SHALL rely on Laravel's built-in `Illuminate\Notifications\DatabaseNotification` model (and notifiable relationships) instead of a custom `App\Models\Notification` subclass.

#### Scenario: Referencing notifications in application code
- **WHEN** application code needs to query or mutate database notifications
- **THEN** it uses `DatabaseNotification` and explicit query conditions rather than custom scopes/helpers

### Requirement: No references to removed model remain
The system SHALL not reference `App\Models\Notification` anywhere in the application, database factories/seeders, or test suites.

#### Scenario: Static reference scan
- **WHEN** a repository-wide search is performed for `App\\Models\\Notification`
- **THEN** no matches are returned

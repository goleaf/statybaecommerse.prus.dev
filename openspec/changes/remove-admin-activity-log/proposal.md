# Change: Remove Admin Activity Log capability

## Why
The codebase still ships an Admin Activity Log model, persistence layer, and schema even though current tests and behavior do not require it. Removing it reduces maintenance surface area and avoids carrying an unused audit subsystem.

## What Changes
- Remove the `admin_activity_logs` table and its migration logic.
- Remove the `App\Models\AdminActivityLog` model and the `App\Support\Audit\AdminActivityLogger` service.
- Remove relationships, dependency injection, and translation keys that reference the admin activity log.
- Update or remove tests that assert activity log persistence.

## Impact
- Affected specs: admin-activity-log
- Affected code:
  - `app/Models/AdminActivityLog.php`
  - `app/Support/Audit/AdminActivityLogger.php`
  - `app/Models/Concerns/HasConditionalRelationships.php`
  - `app/Http/Controllers/Frontend/UserController.php`
  - `app/Http/Controllers/Frontend/DataPrivacyController.php`
  - `database/migrations/2025_09_03_000001_add_enhanced_filament_features.php`
  - `lang/*/messages.php`
  - `tests/Feature/Frontend/UserPrivacySettingsTest.php`
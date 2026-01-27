# Change: Remove System Log model and schema

## Why
The `App\Models\SystemLog` model and `system_logs` table appear to be unused in the codebase. Removing them reduces maintenance surface area and avoids carrying dead schema.

## What Changes
- Remove the `App\Models\SystemLog` model.
- Remove `system_logs` table creation and teardown logic from existing migrations.
- Add a forward-only migration to drop `system_logs` if it already exists in deployed environments.
- Update or add tests to ensure no code references the removed model or table.

## Impact
- Affected specs: system-logs
- Affected code:
  - `app/Models/SystemLog.php`
  - `database/migrations/2025_01_28_000001_create_enhanced_filament_system_tables.php`
  - `database/migrations/2025_09_04_000000_enhance_filament_system_final.php`
  - `database/migrations/*_drop_system_logs_table.php`

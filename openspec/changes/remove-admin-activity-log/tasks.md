## 1. Implementation
- [ ] 1.1 Remove `App\Models\AdminActivityLog` and `App\Support\Audit\AdminActivityLogger`.
- [ ] 1.2 Remove `admin_activity_logs` schema creation and teardown from `database/migrations/2025_09_03_000001_add_enhanced_filament_features.php`.
- [ ] 1.3 Remove any relationships or conditional relationships that reference `AdminActivityLog`.
- [ ] 1.4 Remove dependency injection and usage of `AdminActivityLogger` in controllers or services.
- [ ] 1.5 Remove translation keys related to activity logs if they are no longer used.
- [ ] 1.6 Update or remove tests that assert admin activity log persistence.

## 2. Validation
- [ ] 2.1 Run targeted tests for impacted areas.
- [ ] 2.2 Run `composer run test`.
- [ ] 2.3 Run `composer run analyze` and `composer run lint:php`.